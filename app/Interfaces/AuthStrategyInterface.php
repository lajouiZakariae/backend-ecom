<?php

namespace App\Interfaces;
use App\Models\User;

interface AuthStrategyInterface
{
    public function authenticate(array $credentials): User;
}
