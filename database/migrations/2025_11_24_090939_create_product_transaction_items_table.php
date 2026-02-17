<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('product_transaction_items', function (Blueprint $table) {
            $table->id();

            // Batch header
            $table->foreignId('batch_id')
                ->constrained('product_transaction_batches')
                ->cascadeOnDelete();

            // Product involved
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            // Quantity change (+ or -)
            $table->integer('quantity_change');

            // Logs for audit
            $table->integer('before_quantity')->nullable();
            $table->integer('after_quantity')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_transaction_items');
    }
};
