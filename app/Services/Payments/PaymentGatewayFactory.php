<?php

namespace App\Services\Payments;

use App\Exceptions\PaymentException;
use App\Services\Payments\Contracts\PaymentGateway;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    public function __construct(private readonly Container $app) {}

    /**
     * The gateway to use for a donation.
     *
     * Falls back to the simulated gateway when the provider has no credentials,
     * unless the app forbids it (production) or PAYMENTS_SIMULATE=false.
     */
    public function for(string $method): PaymentGateway
    {
        $gateway = $this->exact($method);

        if ($this->shouldSimulate() || $gateway instanceof SimulatedGateway) {
            return $this->app->make(SimulatedGateway::class);
        }

        if (! $gateway->isConfigured()) {
            if ($this->app->environment('production')) {
                throw PaymentException::notConfigured($method);
            }

            return $this->app->make(SimulatedGateway::class);
        }

        return $gateway;
    }

    /**
     * The provider gateway for a method, without simulation.
     */
    public function exact(string $method): PaymentGateway
    {
        return match ($method) {
            'mpesa' => $this->app->make(MpesaGateway::class),
            'paystack' => $this->app->make(PaystackGateway::class),
            'paypal' => $this->app->make(PayPalGateway::class),
            'simulated' => $this->app->make(SimulatedGateway::class),
            default => throw new InvalidArgumentException("Unsupported payment method [{$method}]."),
        };
    }

    /**
     * True when PAYMENTS_SIMULATE is explicitly enabled.
     */
    private function shouldSimulate(): bool
    {
        return config('hoperise.payments.simulate') === true;
    }
}