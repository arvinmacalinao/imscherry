<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where a stock change came from: 'import' (product Excel import, all existing rows)
     * or 'manual' (quantity changed on the product edit form).
     */
    public function up(): void
    {
        Schema::table('product_restock_logs', function (Blueprint $table) {
            $table->string('source', 20)->default('import')->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_restock_logs', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
