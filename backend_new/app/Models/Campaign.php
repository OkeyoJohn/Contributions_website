<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function donations() { return $this->hasMany(Donation::class); }
}
