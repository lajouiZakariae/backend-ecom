<?php

namespace App\Services\Payment;

use App\Interfaces\PaymentProcessorInterface;
use App\Models\User;
use Laravel\Prompts\Output\ConsoleOutput;

class StripePaymentProcessor implements PaymentProcessorInterface
{
    public function payAmountForUser(User $user, float $amount, array $options = []): mixed
    {
        /**
         * @var \Stripe\Customer $stripeUser
         */
        $stripeUser = $user->createOrGetStripeCustomer();

        // if (!$paymentMethod = $user->findPaymentMethod($options['payment_method_id'])) {
        //     $paymentMethod = $user->addPaymentMethod($options['payment_method_id']);
        // }

        // logger()->info($paymentMethod);

        // dd($user->findPaymentMethod($options['payment_method_id']));

        return $user->paymentMethods();

        $paymentIntent = $user->pay($amount, [
            'payment_method' => $paymentMethod->id,
            'confirm' => true,
            'off_session' => true
        ]);

        return $paymentIntent;
    }
}
