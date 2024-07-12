<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('courier_id')->nullable();
            $table->unsignedBigInteger('shipping_address_id');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('courier_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('shipping_address_id')->references('id')->on('shipping_addresses');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }

};
