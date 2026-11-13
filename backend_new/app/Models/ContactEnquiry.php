<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactEnquiry extends Model
{
    protected $guarded = []; // only ever created server-side from validated data
    protected $casts = ['emailed_at' => 'datetime'];

    public function markEmailed(): void
    {
        $this->forceFill(['emailed_at' => now()])->save();
    }
}
