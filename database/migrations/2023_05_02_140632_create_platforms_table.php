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
        Schema::create('platforms', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name')->unique(); // Example: Shopee, Lazada, Shopify
            $table->text('description')->nullable(); // Optional notes
            $table->boolean('is_active')->default(true); // To enable/disable platform
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platforms');
    }
};
