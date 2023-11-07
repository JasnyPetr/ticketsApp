<?php

namespace App\BaLib\Repositories;

use App\BaLib\Base\Repository;
use Nette\Security\Passwords;
use Tracy\Debugger;
use Tracy\ILogger;
class ClientsRepository extends Repository
{
    public function __construct(\Nette\Database\Context $database)
    {
        parent::__construct($database);
    }

    public function getAllClients(int $limit, int $offset)
    {
        return $this->database->table('clients')
            ->limit($limit, $offset)
            ->order('last_name ASC')->fetchAll();
    }

    public function getAllClientsCount()
    {
        return $this->database->table('clients')->count();
    }

    public function getClientById($id)
    {
        return $this->database->table('clients')->where('id = ?', $id)->fetchAll();
    }

    public function getClientByIdLike($id)
    {
        return $this->database->table('clients')->where('id LIKE', '%'.$id.'%')->fetchAll();
    }

    public function getClientByEmail($email)
    {
        return $this->database->table('clients')->where('email LIKE', '%'.$email.'%')->fetchAll();
    }

    public function getClientByFirstNameOrLastName($searchedText)
    {
        return $this->database->table('clients')->whereOr([
            'first_name LIKE' => '%'.$searchedText.'%',
            'last_name LIKE'=> '%'.$searchedText.'%' ])->fetchAll();
    }

    public function addNewClient($values)
    {
        try {
            $this->database->query('INSERT INTO clients ?', [
                'first_name' => $values->firstName,
                'last_name' => $values->lastName,
                'email' => $values->email
            ]);
            return $this->database->getInsertId();
        }catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::ERROR);
            return false;
        }
    }

    public function updateClient($values)
    {
        try {
            $this->database->query('UPDATE clients SET ?', [
                'first_name' => $values->firstName,
                'last_name' => $values->lastName,
                'email' => $values->email
            ], 'WHERE id = ?', $values->id);
            return true;
        }catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::ERROR);
            return false;
        }
    }
}