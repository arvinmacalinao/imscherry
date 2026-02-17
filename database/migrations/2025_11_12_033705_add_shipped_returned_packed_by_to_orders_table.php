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
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('shipped_by')->nullable()->after('deleted_by');
            $table->unsignedBigInteger('returned_by')->nullable()->after('shipped_by');
            $table->unsignedBigInteger('packed_by')->nullable()->after('returned_by');

             // Timestamps
            $table->timestamp('shipped_at')->nullable()->after('packed_by');
            $table->timestamp('returned_at')->nullable()->after('shipped_at');
            $table->timestamp('packed_at')->nullable()->after('returned_at');

            // Add foreign key constraints referencing users table
            $table->foreign('shipped_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('returned_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('packed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['shipped_by']);
            $table->dropForeign(['returned_by']);
            $table->dropForeign(['packed_by']);

            // Then drop columns
            $table->dropColumn(['shipped_by', 'returned_by', 'packed_by']);
        });
    }
};
