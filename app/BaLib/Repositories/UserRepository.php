<?php

namespace App\BaLib\Repositories;

use App\BaLib\Base\Repository;
use Nette\Security\Passwords;
use Tracy\Debugger;
use Tracy\ILogger;

class UserRepository extends Repository
{
    private $passwords;
    public function __construct(\Nette\Database\Context $database, Passwords $passwords)
    {
        parent::__construct($database);
        $this->passwords = $passwords;
    }

    public function getAllUsers()
    {
        return $this->database->table('users');
    }

    public function getUserById($id)
    {
        return $this->database->table('users')->where('id', $id)->fetch();
    }

    public function getUserByUsername($username)
    {
        return $this->database->table('users')->where('username', $username)->fetch();
    }

    public function checkUsernameUnique($username)
    {
        $user = $this->getUserByUsername($username);
        if ($user) {
            return false;
        } else {
            return true;
        }
    }

    public function registerUser($values)
    {
        try {
            $this->database->query('INSERT INTO users ?', [
                'first_name' => $values->first_name,
                'last_name' => $values->last_name,
                'nick_name' => $values->nick_name,
                'username' => $values->username,
                'password' => $this->passwords->hash($values->password)
            ]);
            return true;
        }catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::ERROR);
            return false;
        }
    }

}