<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use App\BaLib\Repositories\ClientsRepository;
use App\BaLib\Entities\Client;
use Tracy\Debugger;
use Tracy\ILogger;

class ClientsManager
{
    private $clientRepository;

    public function __construct
    (
        ClientsRepository $clientRepository
    )
    {
        $this->clientRepository = $clientRepository;
    }

    public function getAllClientsCount()
    {
        try {
            return $this->clientRepository->getAllClientsCount();
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function loadClient($clientId)
    {
        try {
            $clientData = $this->clientRepository->getClientById($clientId);
            if ($clientData) {
                $clientObject = $this->createClientFromDb($clientData, count($clientData));

                if ($clientObject){
                    return $clientObject;
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

    public function loadClients($limit, $offset)
    {
        try {
            $clientsData = $this->clientRepository->getAllClients($limit, $offset);
            if ($clientsData) {
                $clientsObject = $this->createClientFromDb($clientsData, count($clientsData));

                if ($clientsObject){
                    return $clientsObject;
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

    private function createClientFromDb($values, $countRows)
    {
        try {
            if ($countRows > 0) {
                $returnArr = [];
                foreach ($values as $row) {
                    $clientObject = new Client(
                        $row['first_name'],
                        $row['last_name'],
                        $row['email']
                    );
                    $clientObject->id = $row['id'];

                    $returnArr[] = $clientObject;
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

    public function addNewClient($values)
    {
        try {
            return $this->clientRepository->addNewClient($values);
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function saveClient($values)
    {
        try {
            if ($this->clientRepository->updateClient($values)) {
                return true;
            }else{
                return false;
            }

        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function searchClient($searchedText)
    {
        try {
            $emailCharOne = '@';
            $emailCharTwo = '.';

            if (ctype_digit($searchedText)) {
                $result = $this->clientRepository->getClientByIdLike($searchedText);
            } elseif (strpos($searchedText, $emailCharOne) !== false && strpos($searchedText, $emailCharTwo) !== false) {
                $result = $this->clientRepository->getClientByEmail($searchedText);
            } else {
                $result = $this->clientRepository->getClientByFirstNameOrLastName($searchedText);
            }
            if ($result) {
                $resultObject = $this->createClientFromDb($result, count($result));
                if (is_array($resultObject)) {
                    return $resultObject;
                } else {
                    $returnArr = [$resultObject];
                    return $returnArr;
                }
            }else{
                return false;
            }
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }
}