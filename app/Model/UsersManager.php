<?php

namespace App\Model;

use Nette;
use App\BaLib\Repositories\UserRepository;
use App\Model\MyAuthenticator;

class UsersManager
{
    private $userRepository;
    private $authenticator;

    public function __construct
    (
        UserRepository $userRepository,
        MyAuthenticator $authenticator
    )
    {
        $this->userRepository = $userRepository;
        $this->authenticator = $authenticator;
    }

    public function registerUser($values)
    {
        return $this->userRepository->registerUser($values);
    }

    public function getAllUsers()
    {
        return $this->userRepository->getAllUsers();
    }

    public function getUserById($id)
    {
        return $this->userRepository->getUserById($id);
    }

    public function getUserByUsername($username)
    {
        return $this->userRepository->getUserByUsername($username);
    }

    public function checkUsernameUnique($username)
    {
        return $this->userRepository->checkUsernameUnique($username);
    }

}