<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Настраиваемые поля грузов (ТЗ §14.4) — определения и значения.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargo_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('label', 120);
            $table->enum('field_type', ['text', 'number', 'decimal', 'date', 'boolean', 'select', 'textarea']);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cargo_custom_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained('cargo_custom_fields')->cascadeOnDelete();
            $table->unsignedBigInteger('cargo_id');
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 14, 4)->nullable();
            $table->boolean('value_bool')->nullable();
            $table->timestamps();

            $table->index(['cargo_id', 'field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargo_custom_values');
        Schema::dropIfExists('cargo_custom_fields');
    }
};
