<?php

namespace App\BaLib\Entities;

use App\BaLib\Base\Person;

class Client extends Person
{
    public int $id;
    public function __construct
    (
        string $firstName,
        string $lastName,
        string $email
    )
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
    }
}