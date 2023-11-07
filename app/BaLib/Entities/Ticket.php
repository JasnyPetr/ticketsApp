<?php

namespace App\BaLib\Entities;

class Ticket
{
    public int $id;
    public string $path;
    public bool $isActive;
    public string $clientLink;
    public string $firstName;
    public string $lastName;
    public string $actionName;
    public bool $inDate;

    public function __construct
    (
        int $actionId,
        string $code
    )
    {
        $this->actionId = $actionId;
        $this->code = $code;
    }
}