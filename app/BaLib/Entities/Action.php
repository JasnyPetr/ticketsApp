<?php

declare(strict_types=1);

namespace App\BaLib\Entities;

class Action
{
    public int $id;
    public string $name;
    public string $dateEvent;
    public bool $isActive;
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