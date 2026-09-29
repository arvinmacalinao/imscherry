<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stock put back because an already-picked order was cancelled or set to Pending
     * (source = 'cancel') records which order it came from.
     */
    public function up(): void
    {
        Schema::table('product_restock_logs', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('source')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_restock_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
        });
    }
};
