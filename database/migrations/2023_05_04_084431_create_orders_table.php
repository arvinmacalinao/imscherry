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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
           
            // Core order info
            $table->string('order_number')->unique();
            $table->string('invoice_no', 15)->unique();
            $table->timestamp('order_date')->nullable();
            $table->integer('total_products')->default(0);
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('vat', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->unsignedSmallInteger('platform_id')->nullable();
            $table->unsignedSmallInteger('status_id')->nullable(); 

            // Payment
            $table->string('payment_type')->nullable();

            // Customer Info (flattened — no separate table for now)
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_postcode')->nullable();
            $table->string('shipping_country')->nullable();

            // Logistics
            $table->string('tracking_number')->nullable();
            $table->string('courier')->nullable();

            // Platform sync
            $table->timestamp('platform_created_at')->nullable();
            $table->timestamp('platform_updated_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // FKs
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('platform_id')->references('id')->on('platforms')->nullOnDelete();
            $table->foreign('status_id')->references('id')->on('order_statuses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
