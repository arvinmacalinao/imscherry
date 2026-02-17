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
    Schema::create('product_transactions', function (Blueprint $table) {
        $table->id();

        $table->foreignId('product_id')->constrained()->onDelete('cascade');

        $table->enum('type', [
            'add',            // manual stock in
            'remove',         // manual stock out
            'transfer_in',
            'transfer_out',
            'borrowed',
            'returned',
            'free',
            'adjustment',
            'order_sold',     // auto generated from orders
        ]);

        $table->integer('quantity_change'); // + or -

        $table->integer('before_quantity');
        $table->integer('after_quantity');

        $table->text('note')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();

        $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_transactions');
    }
};
