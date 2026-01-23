<?php

declare(strict_types=1);

use App\Enums\AppleSubSubscriptionStatusEnum;
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
        Schema::create('app_users', function (Blueprint $table) {
            $table->id();
            $table->string('ud_id')->index()->comment('From apple take transection it which is uniq one');
            $table->string('apple_subscription_status')->default(AppleSubSubscriptionStatusEnum::NONE)->comment('take from AppleSubSubscriptionStatusEnum');
            $table->text('social_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('current_app_version', 10)->nullable()->default('1.0');
            $table->string('token_id')->nullable();
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('environment')->nullable();
            $table->string('bundle_id')->nullable();
            $table->text('access_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_users');
    }
};
