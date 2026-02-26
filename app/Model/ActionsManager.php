<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use App\BaLib\Repositories\ActionsRepository;
use App\BaLib\Repositories\TicketsRepository;
use App\BaLib\Entities\Action;
use Tracy\Debugger;
use Tracy\ILogger;

class ActionsManager
{
    private $actionsRepository;
    private $ticketsRepository;

    public function __construct
    (
        ActionsRepository $actionsRepository,
        TicketsRepository $ticketsRepository
    )
    {
        $this->actionsRepository = $actionsRepository;
        $this->ticketsRepository = $ticketsRepository;
    }

    public function getAllActionsCount()
    {
        try {
            return $this->actionsRepository->getAllActionsCount();
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function loadActions($limit, $offset)
    {
        try {
            $actionsData = $this->actionsRepository->getAllActions($limit, $offset);
            if ($actionsData) {
                $actionsObject = $this->createActionFromDb($actionsData, count($actionsData));

                if ($actionsObject){
                    return $actionsObject;
                } else {
                    return false;
                }
            }else{
                return [];
            }
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function createActionFromDb($values, $countRows)
    {
        try {
            if ($countRows > 0) {
                $returnArr = [];
                foreach ($values as $row) {
                    $actionObject = new Action(
                        $row['name'],
                        $row['date_event']->format('d.m.Y'),
                        $row['is_active'],
                    );
                    $actionObject->id = $row['id'];
                    $actionObject->countTickets = $this->ticketsRepository->getCountTicketsByActionId($row['id']);
                    $actionObject->countTicketsUsed = $this->ticketsRepository->getCountTicketsUsedByActionId($row['id']);
                    $returnArr[] = $actionObject;
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

    public function getAllActiveActions()
    {
        try {
            $actionsData = $this->actionsRepository->getAllActiveActions();
            if ($actionsData) {
                $actionsObject = $this->createActionFromDb($actionsData, count($actionsData));

                if ($actionsObject){
                    return $actionsObject;
                } else {
                    return false;
                }
            }else{
                return false;
            }
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function changeActionStatusAjax($actionId, $isChangeStatusTickets)
    {
        try {
            $isChangeStatusTickets = $isChangeStatusTickets == 'true' ? true : false;
            $actionData = $this->actionsRepository->getActionById($actionId);
            if ($actionData) {
                $actionObject = $this->createActionFromDb($actionData, count($actionData));

                if ($actionObject){
                    $actionObject = $actionObject[0];
                    $actionObject->isActive = !$actionObject->isActive;
                    $statusUpdateAction = $this->actionsRepository->updateAction($actionObject);
                    if (!$statusUpdateAction) {
                        return false;
                    }
                    if ($isChangeStatusTickets) {
                        $statusUpdateTickets = $this->ticketsRepository->updateTicketsStatusByActionId($actionId, $actionObject->isActive);
                        if (!$statusUpdateTickets) {
                            return false;
                        }
                    }
                    return true;
                } else {
                    return false;
                }
            }else{
                return false;
            }
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function addNewAction($values)
    {
        try {
            return $this->actionsRepository->addNewAction($values);
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    public function deleteActionAjax($actionId)
    {
        try {
            $actionData = $this->actionsRepository->getActionById($actionId);
            if ($actionData) {
                $actionObject = $this->createActionFromDb($actionData, count($actionData));

                if ($actionObject){
                    $actionObject = $actionObject[0];
                    $statusUpdateAction = $this->actionsRepository->deleteAction($actionId);
                    if ($statusUpdateAction) {
                        $path = 'files/qr/akce_'.$actionId.'/';
                        $statusRm = $this->rmdir_recursive($path);
                        if (!$statusRm) {
                            throw new \Exception('Nepodařilo se smazat adresář se vstupenkami');
                        }
                        return true;
                    }else{
                        throw new \Exception('Nepodařilo se smazat akci');
                    }
                } else {
                    throw new \Exception('Nepodařilo se najít akci');
                }
            }else{
                throw new \Exception('Nepodařilo se najít akci');
            }
        }catch (\Exception $e){
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            return false;
        }
    }

    private function rmdir_recursive($directory)
    {
        if (is_dir($directory)) {
            $files = scandir($directory);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    if (is_dir($directory . '/' . $file)) {
                        $this->rmdir_recursive($directory . '/' . $file);
                    } else {
                        unlink($directory . '/' . $file);
                    }
                }
            }
            rmdir($directory);
            return true;
        } else {
            return false;
        }
    }

    public function saveEditedActionAjax($actionId, $actionName, $dateEvent, $isActive)
    {
        try {
            $actionData = $this->actionsRepository->getActionById($actionId);
            if ($actionData) {
                $actionObject = $this->createActionFromDb($actionData, count($actionData));

                if ($actionObject){
                    $actionObject = $actionObject[0];
                    $actionObject->name = $actionName;
                    $actionObject->dateEvent = $dateEvent;
                    $actionObject->isActive = $isActive == 'true' ? true : false;
                    return $this->actionsRepository->updateAction($actionObject);
                } else {
                    return false;
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