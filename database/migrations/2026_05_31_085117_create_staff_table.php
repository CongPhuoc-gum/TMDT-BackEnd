<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_id')->primary();
            $table->enum('position', ['Designer', 'Florist', 'Customer_Support', 'Delivery_Staff']);
            $table->string('department', 100)->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->date('hire_date');
            $table->json('specializations')->nullable();
            $table->boolean('availability')->default(true);
            $table->timestamps();

            $table->foreign('staff_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->index('position');
            $table->index('availability');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};