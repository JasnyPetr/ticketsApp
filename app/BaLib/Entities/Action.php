<?php

namespace App\BaLib\Entities;

class Action
{
    public int $id;
    public int $countTickets;
    public int $countTicketsUsed;

    public function __construct
    (
        string $name,
        string $dateEvent,
        bool $isActive
    )
    {
        $this->name = $name;
        $this->dateEvent = $dateEvent;
        $this->isActive = $isActive;
    }
}