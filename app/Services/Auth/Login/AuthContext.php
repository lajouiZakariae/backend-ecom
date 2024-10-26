<?php

namespace App\Services\Auth\Login;
use App\Interfaces\AuthStrategyInterface;
use App\Models\User;

class AuthContext
{
    private AuthStrategyInterface $authStrategy;

    public function __construct(string $provider)
    {
        switch ($provider) {
            case 'email':
                $this->authStrategy = new EmailAuthStrategy();
                break;
            case 'google':
                $this->authStrategy = new GoogleAuthStrategy();
                break;
            default:
                throw new \InvalidArgumentException('Invalid authentication provider.');
        }

    }

    /**
     * Set the authentication strategy.
     *
     * @param AuthStrategyInterface $strategy
     */
    public function setStrategy(AuthStrategyInterface $authStrategy): void
    {
        $this->authStrategy = $authStrategy;
    }

    /**
     * Authenticate using the set authStrategy.
     *
     * @param array $credentials
     * @return User
     */
    public function authenticate(array $credentials): User
    {
        return $this->authStrategy->authenticate($credentials);
    }
}