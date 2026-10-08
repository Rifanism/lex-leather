<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Singleton table: exactly one row holds every account number the
        // storefront shows at checkout. Seeded by PaymentSettingSeeder.
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->string('ewallet_provider')->nullable();
            $table->string('ewallet_number')->nullable();
            $table->string('ewallet_holder')->nullable();
            $table->string('qris_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
