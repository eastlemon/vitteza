<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Рейсы — центральная сущность учёта перевозок (ТЗ §10.6).
 * cargo_id — без FK-ограничения (цикл с cargos.trip_id, см. миграцию cargos).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->date('load_date');
            $table->date('unload_date')->nullable();
            $table->foreignId('logistic_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('cargo_id')->nullable();
            $table->string('route', 255);
            $table->decimal('weight_t', 10, 2)->nullable();
            $table->decimal('volume_m3', 10, 2)->nullable();
            $table->string('contract_no', 80)->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('act_no', 80)->nullable();
            $table->date('sent_electronic_at')->nullable();
            $table->date('sent_post_at')->nullable();
            $table->boolean('delivered')->default(false);
            $table->date('payment_due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('payment_fact', 12, 2)->default(0);
            $table->string('status', 20)->default('new');
            $table->decimal('income_total', 12, 2)->default(0);
            $table->decimal('income_logist', 12, 2)->default(0);
            $table->boolean('paid_to_logist')->default(false);
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('logistic_id');
            $table->index('status');
            $table->index('load_date');
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
