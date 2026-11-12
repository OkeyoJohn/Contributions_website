<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class PaymentException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        string $message,
        public readonly int $status = 503,
        public readonly array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function notConfigured(string $method): self
    {
        return new self(
            "The {$method} payment method is not configured on the server yet. Add the provider credentials to .env, or choose M-Pesa.",
            status: 503,
            context: ['payment_method' => $method],
        );
    }
}