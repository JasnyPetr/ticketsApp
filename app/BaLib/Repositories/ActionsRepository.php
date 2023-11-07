<?php

namespace App\BaLib\Repositories;

use App\BaLib\Base\Repository;
use Tracy\Debugger;
use Tracy\ILogger;

class ActionsRepository extends Repository
{
    public function __construct(\Nette\Database\Context $database)
    {
        parent::__construct($database);
    }

    public function getAllActions(int $limit, int $offset)
    {
        return $this->database->table('actions')
            ->limit($limit, $offset)
            ->order('(is_active = 1) DESC, date_event ASC')->fetchAll();
    }

    public function getAllActionsCount()
    {
        return $this->database->table('actions')->count();
    }

    public function getAllActiveActions()
    {
        return $this->database->table('actions')->where('date_event >= CURRENT_DATE()')->order('date_event ASC')->fetchAll();
    }

    public function getActionById($id)
    {
        return $this->database->table('actions')->where('id = ?', $id)->fetchAll();
    }

    public function updateAction($action)
    {
        try {
            $date = new \DateTime($action->dateEvent);
            $this->database->query('UPDATE actions SET ?', [
                'name' => $action->name,
                'date_event' => $date,
                'is_active' => $action->isActive
            ], 'WHERE id = ?', $action->id);
            return true;
        }catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::ERROR);
            return false;
        }
    }

    public function updateTicketsStatusByActionId($actionId, $status)
    {
        try {
            $this->database->query('UPDATE tickets SET ?', [
                'is_active' => $status
            ], 'WHERE action_id = ?', $actionId);
            return true;
        }catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::ERROR);
            return false;
        }
    }

    public function addNewAction($values)
    {
        try {
            $this->database->query('INSERT INTO actions ?', [
                'name' => $values->name,
                'date_event' => $values->dateEvent,
                'is_active' => $values->isActive
            ]);
            return $this->database->getInsertId();
        }catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::ERROR);
            return false;
        }
    }

    public function deleteAction($actionId)
    {
        try {
            $this->database->query('DELETE FROM actions WHERE id = ?', $actionId);
            return true;
        }catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::ERROR);
            return false;
        }
    }
}