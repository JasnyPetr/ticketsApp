<?php

declare(strict_types=1);

namespace App\BaLib\Base;

abstract class Repository
{
    protected $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }
}