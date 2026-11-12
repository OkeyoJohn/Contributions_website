<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('title');
            $t->string('description');
            $t->text('long_description')->nullable();
            $t->string('image_url')->nullable();
            $t->unsignedBigInteger('goal_amount')->default(0);   // KES
            $t->unsignedBigInteger('raised_amount')->default(0); // KES, only increased by verified payments
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('campaigns'); }
};
