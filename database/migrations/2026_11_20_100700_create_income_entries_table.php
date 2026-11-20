<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Начисления дохода логисту — основа «обнуления» дашборда по месяцам (ТЗ §15.5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->decimal('income_total', 12, 2);
            $table->decimal('income_logist', 12, 2);
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'period_year', 'period_month']);
            $table->index('trip_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_entries');
    }
};
