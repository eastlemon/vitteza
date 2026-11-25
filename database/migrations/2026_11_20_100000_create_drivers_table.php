<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drivers and vehicles directory (Spec §11.5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 180);
            $table->string('plate_number', 20)->nullable();
            $table->text('passport_data')->nullable();          // sensitive (§11.4)
            $table->string('vehicle_cert_no', 40)->nullable();
            $table->string('vehicle_owner', 180)->nullable();
            $table->string('license_series_no', 40)->nullable(); // sensitive (§11.4)
            $table->date('license_expiry')->nullable();
            $table->decimal('capacity_t', 8, 2)->nullable();
            $table->decimal('volume_m3', 8, 2)->nullable();
            $table->string('body_params', 120)->nullable();
            $table->text('ip_requisites')->nullable();           // sensitive (§11.4)
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'full_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
