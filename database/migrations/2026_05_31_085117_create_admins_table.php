<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->primary();
            $table->enum('role', ['SuperAdmin', 'Manager'])->default('Manager');
            $table->json('permissions')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};