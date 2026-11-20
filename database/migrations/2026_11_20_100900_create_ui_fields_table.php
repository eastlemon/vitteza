<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Динамические поля вкладок (ТЗ §17.5) — код вкладки, код поля, label, тип,
 * видимость, порядок. Filament-ресурсы могут строить схему формы/колонок из этой таблицы.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ui_fields', function (Blueprint $table) {
            $table->id();
            $table->string('tab_code', 60);
            $table->string('field_code', 80);
            $table->string('label', 120);
            $table->enum('type', ['text', 'number', 'decimal', 'date', 'boolean', 'select', 'textarea', 'relation']);
            $table->boolean('is_visible')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tab_code', 'is_visible', 'sort_order']);
            $table->unique(['tab_code', 'field_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ui_fields');
    }
};
