<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $reference
 * @property int|null $campaign_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property int $amount
 * @property string $currency
 * @property string $payment_method
 * @property string $status
 * @property bool $anonymous
 * @property string|null $message
 * @property string|null $provider_reference
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property array<string, mixed>|null $meta
 */
#[Fillable([
    'reference', 'campaign_id', 'name', 'email', 'phone', 'amount', 'currency',
    'payment_method', 'status', 'anonymous', 'message', 'provider_reference', 'paid_at', 'meta',
])]
class Donation extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const PAYMENT_METHODS = ['mpesa', 'paystack', 'paypal'];

    /**
     * The campaign this donation was given to.
     *
     * @return BelongsTo<Campaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'anonymous' => 'boolean',
            'paid_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }
}