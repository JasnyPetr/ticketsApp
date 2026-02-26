<?php

declare(strict_types=1);

namespace App\BaLib\Interfaces;

interface IClientSearch
{
    public function handleSearch(string $searchedText): void;
}