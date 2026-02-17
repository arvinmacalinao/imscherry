<?php

use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignIdFor(User::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('from_status_id')->nullable();
            $table->unsignedSmallInteger('to_status_id');
            $table->foreign('from_status_id')->references('id')->on('order_statuses')->nullOnDelete();
            $table->foreign('to_status_id')->references('id')->on('order_statuses')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scan_logs');
    }
};
