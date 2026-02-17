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
        Schema::create('product_pulls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')   
                  ->constrained()            
                  ->onDelete('cascade');    
            $table->foreignId('employee_id') 
                  ->constrained('users')     
                  ->onDelete('cascade');    
            $table->integer('quantity');     
            $table->timestamp('pulled_at')    
                  ->useCurrent();           
            $table->string('status')->default('completed'); 
            $table->timestamps();         
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_pulls');
    }
};
