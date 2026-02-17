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
        Schema::create('product_restock_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id'); // link to products table
            $table->integer('old_quantity')->default(0); // previous stock
            $table->integer('added_quantity'); // how much was added
            $table->integer('new_quantity'); // final stock after restock
            $table->unsignedBigInteger('user_id')->nullable(); // who did the restock (if applicable)
            $table->timestamps();

            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
