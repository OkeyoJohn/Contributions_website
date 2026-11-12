<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Donation extends Model
{
    protected $guarded = []; // only ever created server-side from validated data
    protected $casts = ['amount' => 'decimal:2', 'anonymous' => 'boolean', 'provider_payload' => 'array', 'paid_at' => 'datetime'];

    public function campaign() { return $this->belongsTo(Campaign::class); }

    /**
     * The ONLY place a donation becomes paid. Idempotent: webhooks and polling can both
     * call it, but the campaign total is incremented exactly once.
     */
    public function markPaid(array $payload = []): bool
    {
        return DB::transaction(function () use ($payload) {
            $d = static::whereKey($this->id)->lockForUpdate()->first();
            if ($d->status === 'paid') { $this->refresh(); return false; }

            $d->update([
                'status' => 'paid',
                'paid_at' => now(),
                'provider_payload' => array_merge($d->provider_payload ?? [], ['confirmation' => $payload]),
            ]);
            if ($d->campaign_id) {
                Campaign::whereKey($d->campaign_id)->increment('raised_amount', (int) round($d->amount));
            }
            $this->refresh();
            return true;
        });
    }

    public function markFailed(array $payload = []): void
    {
        static::whereKey($this->id)->where('status', 'pending')->update([
            'status' => 'failed',
            'provider_payload' => json_encode(array_merge($this->provider_payload ?? [], ['failure' => $payload])),
        ]);
        $this->refresh();
    }
}
