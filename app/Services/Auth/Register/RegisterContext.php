<?php

namespace App\Services\Auth\Register;
use App\Interfaces\AuthRegisterInterface;
use App\Models\User;

class RegisterContext
{
    private AuthRegisterInterface $authStrategy;

    public function __construct(string $provider)
    {
        switch ($provider) {
            case 'email':
                $this->authStrategy = new EmailRegisterStrategy();
                break;
            case 'google':
                $this->authStrategy = new GoogleRegisterStrategy();
                break;
            default:
                throw new \InvalidArgumentException('Invalid authentication provider.');
        }
    }

    /**
     * Set the authentication strategy.
     *
     * @param AuthRegisterInterface $strategy
     */
    public function setStrategy(AuthRegisterInterface $authStrategy): void
    {
        $this->authStrategy = $authStrategy;
    }

    /**
     * Authenticate using the set authStrategy.
     *
     * @param array $payload
     * @return User
     */
    public function register(array $payload): User
    {
        return $this->authStrategy->register($payload);
    }
}