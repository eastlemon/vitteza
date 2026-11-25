<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer contract history (Spec §13.4/§13.5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('contract_no', 80);
            $table->date('signed_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('subject', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('contract_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_contracts');
    }
};
