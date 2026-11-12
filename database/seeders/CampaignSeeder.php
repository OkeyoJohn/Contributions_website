<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    /**
     * The campaigns shown on the public site until real ones are managed in a CMS.
     */
    public function run(): void
    {
        $campaigns = [
            [
                'slug' => 'education-for-every-child',
                'title' => 'Education for Every Child',
                'description' => 'School fees, uniforms and books for children who would otherwise stay home.',
                'long_description' => 'Every child deserves a classroom. This campaign pays school fees, buys uniforms and provides textbooks and stationery for children from families that cannot afford them. We work with local schools to identify the pupils who are most at risk of dropping out, and we cover a full school year for each child we support.',
                'goal_amount' => 500000,
                'raised_amount' => 312500,
                'image_url' => 'https://images.unsplash.com/photo-1497486751825-1233686d5d80?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'slug' => 'feed-a-family',
                'title' => 'Feed a Family',
                'description' => 'Monthly food packages for families facing hunger.',
                'long_description' => 'A monthly food package keeps a family of five going for weeks: maize flour, rice, beans, cooking oil, sugar, tea and fresh produce where it is available. The packages are packed and handed over with community volunteers, and each family is visited again the following month.',
                'goal_amount' => 300000,
                'raised_amount' => 186400,
                'image_url' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'slug' => 'community-healthcare',
                'title' => 'Community Healthcare',
                'description' => 'Mobile clinics and essential medicine for remote communities.',
                'long_description' => 'Mobile clinics bring a nurse, a clinician and essential medicine to communities that are hours away from the nearest hospital. The campaign funds fuel, medicine, basic equipment and the health talks that run alongside each visit.',
                'goal_amount' => 800000,
                'raised_amount' => 445000,
                'image_url' => 'https://images.unsplash.com/photo-1576765608535-5f04d1e3f289?auto=format&fit=crop&w=900&q=80',
            ],
        ];

        foreach ($campaigns as $campaign) {
            Campaign::query()->updateOrCreate(
                ['slug' => $campaign['slug']],
                $campaign + ['is_active' => true],
            );
        }
    }
}