<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Enums\Currency;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('bank_account_id')->index();
            $table->bigInteger('amount_cents');
            $table->enum('currency', array_map(fn($c) => $c->value, Currency::cases()))->default(Currency::SAR->value);
            $table->timestamp('date');
            $table->string('reference')->index();
            $table->json('meta')->nullable(); // Key/value pairs from webhook
            $table->timestamps();

            // To prevent duplicates
            $table->unique(['reference', 'bank_account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
