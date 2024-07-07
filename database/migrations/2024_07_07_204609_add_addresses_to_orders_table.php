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
        Schema::table('orders', function (Blueprint $table) {
            // Check if the billing_address_id column does not exist before adding
            if (!Schema::hasColumn('orders', 'billing_address_id')) {
                $table->unsignedBigInteger('billing_address_id')->nullable();
                $table->foreign('billing_address_id')->references('id')->on('billing_addresses')->onDelete('set null');
            }

            // Check if the shipping_address_id column does not exist before adding
            if (!Schema::hasColumn('orders', 'shipping_address_id')) {
                $table->unsignedBigInteger('shipping_address_id')->nullable();
                $table->foreign('shipping_address_id')->references('id')->on('shipping_addresses')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'billing_address_id')) {
                $table->dropForeign(['billing_address_id']);
                $table->dropColumn('billing_address_id');
            }

            if (Schema::hasColumn('orders', 'shipping_address_id')) {
                $table->dropForeign(['shipping_address_id']);
                $table->dropColumn('shipping_address_id');
            }
        });
    }
};
