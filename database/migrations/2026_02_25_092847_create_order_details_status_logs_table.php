<?php

use App\Models\OrderDetails;
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
        Schema::create('order_details_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(OrderDetails::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('status_id')->nullable(); 
            $table->foreign('status_id')->references('id')->on('order_statuses')->nullOnDelete();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at')->useCurrent();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details_status_logs');
    }
};
