<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property string|null $long_description
 * @property int $goal_amount
 * @property int $raised_amount
 * @property string|null $image_url
 * @property bool $is_active
 */
#[Fillable(['slug', 'title', 'description', 'long_description', 'goal_amount', 'raised_amount', 'image_url', 'is_active'])]
class Campaign extends Model
{
    /**
     * Donations made towards this campaign.
     *
     * @return HasMany<Donation, $this>
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'goal_amount' => 'integer',
            'raised_amount' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}