<?php

namespace Tests\Feature\Api;

use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_a_donation_and_confirms_it(): void
    {
        $campaign = Campaign::query()->create([
            'slug' => 'education-for-every-child',
            'title' => 'Education for Every Child',
            'description' => 'School fees and books.',
            'goal_amount' => 500000,
            'raised_amount' => 100000,
        ]);

        $response = $this->postJson('/api/donations', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0712 345 678',
            'amount' => 1000,
            'currency' => 'KES',
            'campaign_id' => $campaign->id,
            'anonymous' => false,
            'message' => 'For the children',
            'payment_method' => 'mpesa',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', Donation::STATUS_PAID)
            ->assertJsonPath('data.amount', 1000)
            ->assertJsonPath('data.payment_method', 'mpesa')
            ->assertJsonStructure(['data' => ['reference', 'status', 'redirect_url']]);

        $reference = $response->json('data.reference');

        $this->assertDatabaseHas('donations', [
            'reference' => $reference,
            'status' => Donation::STATUS_PAID,
            'campaign_id' => $campaign->id,
            'paid_at' => now(),
        ]);

        $this->assertSame(101000, $campaign->fresh()->raised_amount);
    }

    public function test_it_returns_the_donation_by_reference(): void
    {
        $donation = $this->createDonation();

        $this->getJson("/api/donations/{$donation->reference}")
            ->assertOk()
            ->assertJsonPath('data.reference', $donation->reference)
            ->assertJsonPath('data.status', Donation::STATUS_PAID);
    }

    public function test_it_returns_not_found_for_an_unknown_reference(): void
    {
        $this->getJson('/api/donations/HR-UNKNOWN')->assertNotFound();
    }

    public function test_it_validates_the_donation_payload(): void
    {
        $this->postJson('/api/donations', [
            'name' => 'A',
            'email' => 'not-an-email',
            'phone' => '12345',
            'amount' => 10,
            'campaign_id' => 999,
            'payment_method' => 'bitcoin',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone', 'amount', 'campaign_id', 'payment_method']);
    }

    public function test_it_requires_an_mpesa_phone_number(): void
    {
        $this->postJson('/api/donations', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'amount' => 500,
            'payment_method' => 'mpesa',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_it_does_not_double_count_a_repeated_confirmation(): void
    {
        $campaign = Campaign::query()->create([
            'slug' => 'feed-a-family',
            'title' => 'Feed a Family',
            'description' => 'Monthly food packages.',
            'goal_amount' => 300000,
            'raised_amount' => 0,
        ]);

        $donation = $this->createDonation($campaign);

        $service = app(\App\Services\DonationService::class);
        $service->applyStatus($donation, Donation::STATUS_PAID);

        $this->assertSame(500, $campaign->fresh()->raised_amount);
    }

    private function createDonation(?Campaign $campaign = null): Donation
    {
        $response = $this->postJson('/api/donations', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0712345678',
            'amount' => 500,
            'campaign_id' => $campaign?->id,
            'payment_method' => 'mpesa',
        ])->assertCreated();

        return Donation::query()->where('reference', $response->json('data.reference'))->firstOrFail();
    }
}