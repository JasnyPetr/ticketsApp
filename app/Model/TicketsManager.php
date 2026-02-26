<?php

declare(strict_types=1);

namespace App\Model;

use App\BaLib\Entities\Ticket;
use App\BaLib\Repositories\TicketsRepository;
use Nette\Application\LinkGenerator;
use Nette;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Tracy\Debugger;
use Tracy\ILogger;

class TicketsManager
{
    private $ticketsRepository;
    private $linkGenerator;

    public function __construct
    (
        TicketsRepository $ticketsRepository,
        LinkGenerator $linkGenerator
    )
    {
        $this->ticketsRepository = $ticketsRepository;
        $this->linkGenerator = $linkGenerator;
    }

    public function getActionDetailAjax($actionId)
    {
        try {
            $ticketsData = $this->ticketsRepository->getTicketsByActionId($actionId);
            if ($ticketsData) {
                $ticketObject = $this->createTicketFromDb($ticketsData, count($ticketsData));

                if ($ticketObject){
                    return $ticketObject;
                } else {
                    return false;
                }
            }else {
                return false;
            }
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function createTicketFromDb($values, $countRows)
    {
        try {
            if ($countRows > 0) {
                $returnArr = [];
                foreach ($values as $row) {
                    $ticketObject = new Ticket(
                        $row['action_id'],
                        $row['code']
                    );
                    $ticketObject->id = $row['id'];
                    $ticketObject->path = $row['path'];
                    $ticketObject->isActive = $row['is_active'];
                    if (isset($row['clientId']) && isset($row['first_name']) && isset($row['last_name'])) {
                        $ticketObject->clientLink = $this->linkGenerator->link('Admin:Clients:detail', ['id' => $row['clientId']]);
                        $ticketObject->firstName = $row['first_name'];
                        $ticketObject->lastName = $row['last_name'];
                    }
                    if (isset($row['actionName'])) {
                        $ticketObject->actionName = $row['actionName'];
                    }
                    if (isset($row['dateEvent'])) {
                        $today = new \DateTime();
                        $dateEvent = new \DateTime($row['dateEvent']);
                        if ($today->format('Y-m-d') === $dateEvent->format('Y-m-d')) {
                            $ticketObject->inDate = true;
                        } else {
                            $ticketObject->inDate = false;
                        }
                    }

                    $returnArr[] = $ticketObject;
                }

                return $returnArr;
            } else {
                return false;
            }
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function getTicketsByClientId($clientId)
    {
        try {
            $ticketsData = $this->ticketsRepository->getTicketsByClientId($clientId);
            if ($ticketsData) {
                $ticketObject = $this->createTicketFromDb($ticketsData, count($ticketsData));

                if ($ticketObject){
                    return $ticketObject;
                } else {
                    return false;
                }
            }else {
                return false;
            }
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function changeTicketStatusAjax($ticketId)
    {
        try {
            $ticketData = $this->ticketsRepository->getTicketById($ticketId);
            if ($ticketData) {
                $ticketObject = $this->createTicketFromDb($ticketData, count($ticketData));

                if ($ticketObject){
                    $ticketObject = $ticketObject[0];
                    $ticketObject->isActive = !$ticketObject->isActive;
                    return $this->ticketsRepository->updateTicket($ticketObject);
                } else {
                    return false;
                }
            }else {
                return false;
            }
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function getTicketPathById($ticketId)
    {
        try {
            $ticketData = $this->ticketsRepository->getTicketById($ticketId);
            if ($ticketData) {
                $ticketObject = $this->createTicketFromDb($ticketData, count($ticketData));
                if ($ticketObject) {
                    return $ticketObject[0]->path;
                }
            }
            return false;
        } catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function deleteTicketAjax($ticketId)
    {
        try {
            $ticketPath = $this->getTicketPathById($ticketId);
            if ($ticketPath !== false && file_exists($ticketPath)) {
                unlink($ticketPath);
            }
            return $this->ticketsRepository->deleteTicket($ticketId);
        } catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function createNewTicketAjax($actionId, $clientId)
    {
        try {
            $code = $this->generateRandomCode();
            $newTicket = new Ticket(
                $actionId,
                $code
            );

            $filePath = $this->createQrCode($code, $actionId);

            if ($filePath !== false) {
                $newTicket->path = $filePath;
            } else {
                return false;
            }

            $newTicket->isActive = true;

            $insertedId = $this->ticketsRepository->insertNewTicket($newTicket);

            if ($insertedId !== false) {
                return $this->ticketsRepository->insertNewTicketClientXref($insertedId, $clientId);
            } else {
                return false;
            }

        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    private function createQrCode($code, $actionId)
    {
        try {
            $options = new QROptions([
                'version' => 5,
                'imageTransparent' => false,
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'   => QRCode::ECC_H,
            ]);

            if (!file_exists('./files/qr/akce_'. $actionId)) {
                mkdir('./files/qr/akce_'.$actionId, 0755, true);
            }

            $qrcode = new QRCode($options);
            $path = 'files/qr/akce_'.$actionId.'/'.$code.'.png';
            $qrcode->render($code, 'files/qr/akce_'.$actionId.'/'.$code.'.png');

            return $path;
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    private function generateRandomCode($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $randomIndex = random_int(0, strlen($characters) - 1);
            $code .= $characters[$randomIndex];
        }

        return $code;
    }

    public function analyzeLoadedCodeAjax($qrcode)
    {
        try {
            $ticketData = $this->ticketsRepository->getTicketByCode($qrcode);
            if ($ticketData) {
                $ticketObject = $this->createTicketFromDb($ticketData, count($ticketData));

                if ($ticketObject){
                    return $ticketObject;
                } else {
                    return false;
                }
            }else {
                return false;
            }
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function decideOfTicketAjax($qrcode)
    {
        try {
            $ticketData = $this->ticketsRepository->getTicketByCode($qrcode);
            if ($ticketData) {
                $ticketObject = $this->createTicketFromDb($ticketData, count($ticketData));

                if ($ticketObject){
                    $ticketObject = $ticketObject[0];
                    if ($ticketObject->isActive) {
                        $ticketObject->isActive = false;
                        return $this->ticketsRepository->updateTicket($ticketObject);
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }else {
                return false;
            }
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }
}