<?php

namespace Tests\Feature\Api;

use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_active_campaigns(): void
    {
        Campaign::query()->create([
            'slug' => 'education-for-every-child',
            'title' => 'Education for Every Child',
            'description' => 'School fees and books.',
            'goal_amount' => 500000,
            'raised_amount' => 1000,
        ]);

        Campaign::query()->create([
            'slug' => 'hidden-campaign',
            'title' => 'Hidden',
            'description' => 'Not published.',
            'goal_amount' => 1000,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/campaigns');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'education-for-every-child')
            ->assertJsonPath('data.0.progress', 0);
    }

    public function test_it_shows_a_campaign_by_slug(): void
    {
        Campaign::query()->create([
            'slug' => 'feed-a-family',
            'title' => 'Feed a Family',
            'description' => 'Monthly food packages.',
            'goal_amount' => 300000,
            'raised_amount' => 150000,
        ]);

        $this->getJson('/api/campaigns/feed-a-family')
            ->assertOk()
            ->assertJsonPath('data.title', 'Feed a Family')
            ->assertJsonPath('data.progress', 50);
    }

    public function test_it_returns_not_found_for_an_unknown_slug(): void
    {
        $this->getJson('/api/campaigns/unknown')->assertNotFound();
    }

    public function test_it_returns_not_found_for_an_inactive_campaign(): void
    {
        Campaign::query()->create([
            'slug' => 'hidden-campaign',
            'title' => 'Hidden',
            'description' => 'Not published.',
            'goal_amount' => 1000,
            'is_active' => false,
        ]);

        $this->getJson('/api/campaigns/hidden-campaign')->assertNotFound();
    }
}