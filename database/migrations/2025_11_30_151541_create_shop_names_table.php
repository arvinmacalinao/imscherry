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
        Schema::create('shop_names', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('platform_id');
            $table->foreign('platform_id')
                  ->references('id')
                  ->on('platforms')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_names');
    }
};
