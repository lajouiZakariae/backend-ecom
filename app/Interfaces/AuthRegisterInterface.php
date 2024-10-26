<?php

namespace App\Interfaces;
use App\Models\User;

interface AuthRegisterInterface
{
    public function register(array $userData): User;
}
