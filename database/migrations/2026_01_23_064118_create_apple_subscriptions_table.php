<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apple_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');

            // Apple identifiers
            $table->string('transaction_id')->nullable()->index();
            $table->string('original_transaction_id')->index();
            $table->string('product_id');

            // Status & environment
            $table->string('environment');
            $table->string('status');

            // Dates
            $table->timestamp('purchase_date')->nullable();
            $table->timestamp('expires_date')->nullable();

            // Renewal details
            $table->string('price_currency')->nullable();
            $table->decimal('price_amount', 12, 2)->nullable();

            $table->json('raw_transaction')->nullable()->comment('decoded signedTransactionInfo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apple_subscriptions');
    }
};
