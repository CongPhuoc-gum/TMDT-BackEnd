<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('fullname', 100);
            $table->string('email', 100)->unique();
            $table->string('password_hash', 255)->nullable();
            $table->string('phone', 10)->nullable();
            $table->string('avatar_url', 255)->nullable();
            $table->string('otp_code', 6)->nullable();
            $table->dateTime('otp_expires_at')->nullable();
            $table->string('google_id', 255)->nullable()->unique();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index('email');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};