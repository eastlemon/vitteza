<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Справочник заказчиков и реквизиты для документооборота (ТЗ §13.5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('full_legal_name', 255)->nullable();
            $table->string('inn', 15)->nullable();
            $table->string('kpp', 10)->nullable();
            $table->string('ogrn', 15)->nullable();
            $table->string('legal_address', 255)->nullable();
            $table->string('postal_address', 255)->nullable();
            $table->string('account_number', 30)->nullable();
            $table->string('bank_name', 180)->nullable();
            $table->string('bank_bik', 10)->nullable();
            $table->string('correspondent_account', 30)->nullable();
            $table->string('contact_person', 120)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_email', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('inn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
