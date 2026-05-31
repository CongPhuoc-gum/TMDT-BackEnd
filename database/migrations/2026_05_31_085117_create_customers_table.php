<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->primary();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('Vietnam');
            $table->integer('loyalty_points')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0.00);
            $table->string('preferred_delivery_time', 50)->default('Morning');
            $table->timestamps();

            $table->foreign('customer_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};