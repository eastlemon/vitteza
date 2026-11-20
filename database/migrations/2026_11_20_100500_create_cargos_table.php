<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Экземпляры грузов в рейсах (ТЗ §14.6).
 * trip_id — без FK-ограничения, чтобы избежать цикла с trips.cargo_id
 * (обе связи создаются в одной транзакции приложения).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('cargo_templates')->nullOnDelete();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->string('name', 180);
            $table->string('cargo_class', 40)->default('general');
            $table->decimal('weight_t', 10, 2)->nullable();
            $table->decimal('volume_m3', 10, 2)->nullable();
            $table->string('packaging', 80)->nullable();
            $table->integer('pieces')->nullable();
            $table->string('dimensions', 60)->nullable();
            $table->string('temperature_regime', 60)->nullable();
            $table->text('special_conditions')->nullable();
            $table->decimal('declared_value', 12, 2)->nullable();
            $table->timestamps();

            $table->index('trip_id');
            $table->index('template_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargos');
    }
};
