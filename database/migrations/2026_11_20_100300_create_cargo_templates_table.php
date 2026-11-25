<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cargo templates — typical cargo with parameters (Spec §14.6).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargo_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('cargo_class', 40)->default('general');
            $table->text('description')->nullable();
            $table->decimal('default_weight_t', 10, 2)->nullable();
            $table->decimal('default_volume_m3', 10, 2)->nullable();
            $table->string('default_packaging', 80)->nullable();
            $table->integer('default_pieces')->nullable();
            $table->string('default_dimensions', 60)->nullable();
            $table->string('temperature_regime', 60)->nullable();
            $table->text('special_conditions')->nullable();
            $table->decimal('declared_value', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargo_templates');
    }
};
