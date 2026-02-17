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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(\App\Models\Order::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(\App\Models\Product::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->string('product_name');
            $table->string('sku');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
