<?php

declare(strict_types=1);

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
            $table->string('ud_id')->index();
            $table->string('token_id');
            $table->text('social_id')->nullable();
            $table->string('ip')->nullable();
            $table->string('current_app_version', 10)->nullable()->default('1');
            $table->string('token_id')->nullable()->change();
            $table->string('email')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
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
