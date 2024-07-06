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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->integer('total_quantity')->default(0);
            $table->decimal('total_price', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamp('completed_at')->nullable();
            $table->string('discount_code')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('payment_status')->nullable();            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
