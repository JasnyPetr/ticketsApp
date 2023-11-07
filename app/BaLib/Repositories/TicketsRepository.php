<?php

namespace App\BaLib\Repositories;

use App\BaLib\Base\Repository;

class TicketsRepository extends Repository
{
    public function __construct(\Nette\Database\Context $database)
    {
        parent::__construct($database);
    }

    public function getAllTickets(int $limit, int $offset)
    {
        return $this->database->table('tickets')
            ->limit($limit, $offset)
            ->order('action_id DESC')->fetchAll();
    }

    public function getAllTicketsCount()
    {
        return $this->database->table('tickets')->count();
    }

    public function getTicketById($id)
    {
        return $this->database->table('tickets')->where('id = ?', $id)->fetchAll();
    }

    public function getTicketsByActionId($actionId)
    {
        return $this->database->fetchAll('SELECT tickets.*, cl.id as clientId, cl.first_name, cl.last_name FROM tickets LEFT JOIN clients_tickets_xref AS ctx ON ctx.ticket_id = tickets.id LEFT JOIN clients AS cl ON cl.id = ctx.client_id WHERE action_id = ?', $actionId);
    }

    public function getTicketsByClientId($clientId)
    {
        return $this->database->fetchAll('SELECT tickets.*, actions.name as actionName FROM tickets LEFT JOIN clients_tickets_xref AS ctx ON ctx.ticket_id = tickets.id LEFT JOIN actions ON actions.id = tickets.action_id WHERE ctx.client_id = ?', $clientId);
    }

    public function insertNewTicket($tiket)
    {
        try {
            $this->database->query('INSERT INTO tickets ?', [
                'action_id' => $tiket->actionId,
                'code' => $tiket->code,
                'path' => $tiket->path,
                'is_active' => $tiket->isActive
            ]);
            return $this->database->getInsertId();
        }catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::ERROR);
            return false;
        }
    }

    public function updateTicket($ticket)
    {
        try {
            $this->database->query('UPDATE tickets SET ?', [
                'action_id' => $ticket->actionId,
                'code' => $ticket->code,
                'path' => $ticket->path,
                'is_active' => $ticket->isActive
            ], 'WHERE id = ?', $ticket->id);
            return true;
        }catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::ERROR);
            return false;
        }
    }

    public function insertNewTicketClientXref($ticketId, $clientId)
    {
        try {
            $this->database->query('INSERT INTO clients_tickets_xref ?', [
                'ticket_id' => $ticketId,
                'client_id' => $clientId
            ]);
            return true;
        }catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::ERROR);
            return false;
        }
    }

    public function deleteTicket($ticketId)
    {
        try {
            $this->database->query('DELETE FROM tickets WHERE id = ?', $ticketId);
            return true;
        }catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::ERROR);
            return false;
        }
    }

    public function getCountTicketsByActionId($actionId)
    {
        return $this->database->table('tickets')->where('action_id = ?', $actionId)->count();
    }

    public function getCountTicketsUsedByActionId($actionId)
    {
        return $this->database->table('tickets')->where('action_id = ? AND is_active = ?', $actionId, false)->count();
    }

    public function getTicketByCode($code)
    {
        return $this->database->fetchAll('SELECT tickets.*, cl.id as clientId, cl.first_name, cl.last_name, actions.name as actionName, actions.date_event as dateEvent FROM tickets LEFT JOIN clients_tickets_xref AS ctx ON ctx.ticket_id = tickets.id LEFT JOIN clients AS cl ON cl.id = ctx.client_id LEFT JOIN actions ON actions.id = tickets.action_id WHERE tickets.code = ?', $code);
    }
}