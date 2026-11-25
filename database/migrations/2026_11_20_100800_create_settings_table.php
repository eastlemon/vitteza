<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Parameterized system settings (Spec §15.4): finance formula,
 * rating weights, income trigger status, etc.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 120)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Default values (Spec §15.4, §16.2).
        \DB::table('settings')->insert([
            ['key' => 'income_share',          'value' => '0.20',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'logist_share',          'value' => '0.50',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'income_trigger_status', 'value' => 'paid',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'rating_w_trips',        'value' => '0.40',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'rating_w_income',       'value' => '0.60',  'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
