<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['education-for-every-child', 'Education for Every Child', 'School fees, uniforms and books for children who would otherwise stay home.', 500000, 'photo-1497486751825-1233686d5d80'],
            ['feed-a-family', 'Feed a Family', 'Monthly food packages for families facing hunger.', 300000, 'photo-1488521787991-ed7bbaae773c'],
            ['community-healthcare', 'Community Healthcare', 'Mobile clinics and essential medicine for remote communities.', 800000, 'photo-1576765608535-5f04d1e3f289'],
        ];
        foreach ($rows as [$slug, $title, $desc, $goal, $img]) {
            Campaign::updateOrCreate(['slug' => $slug], [
                'title' => $title, 'description' => $desc, 'goal_amount' => $goal,
                'image_url' => "https://images.unsplash.com/$img?auto=format&fit=crop&w=900&q=80",
            ]);
        }
    }
}
