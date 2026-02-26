<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use App\BaLib\Repositories\UserRepository;

class UsersManager
{
    private $userRepository;

    public function __construct
    (
        UserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
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