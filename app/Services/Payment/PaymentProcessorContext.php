<?php

namespace App\Services\Payment;

use App\Interfaces\PaymentProcessorInterface;
use App\Models\User;

class PaymentProcessorContext
{
    private PaymentProcessorInterface $paymentProcessorStrategy;

    public function __construct(string $provider)
    {
        switch ($provider) {
            case 'stripe':
                $this->paymentProcessorStrategy = new StripePaymentProcessor();
                break;
            default:
                throw new \InvalidArgumentException('Invalid payment processor.');
        }
    }

    /**
     * Set the authentication strategy.
     *
     * @param PaymentProcessorInterface $strategy
     */
    public function setStrategy(PaymentProcessorInterface $paymentProcessorStrategy): void
    {
        $this->paymentProcessorStrategy = $paymentProcessorStrategy;
    }


    public function payAmountForUser(User $user, float $amount, array $options = []): mixed
    {
        return $this->paymentProcessorStrategy->payAmountForUser($user, $amount, $options);
    }
}