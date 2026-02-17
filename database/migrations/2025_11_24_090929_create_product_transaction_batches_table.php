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
        Schema::create('product_transaction_batches', function (Blueprint $table) {
            $table->id();
            $table->timestamp('transaction_date')->nullable();
            // belongs to product_transaction_types
            $table->foreignId('transaction_type_id')
                ->constrained('product_transaction_types')
                ->cascadeOnDelete();
            
            $table->text('note')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_transaction_batches');
    }
};
