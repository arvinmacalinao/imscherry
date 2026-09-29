<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Brand of the products in a category (CHERRY, LUXELLE), used to group the reports.
     * Categories of other brands stay empty and show as "Other".
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('brand', 50)->nullable()->after('name')->index();
        });

        DB::table('categories')->where('name', 'like', 'CHERRY%')->update(['brand' => 'CHERRY']);
        DB::table('categories')->where('name', 'like', 'LUXELLE%')->update(['brand' => 'LUXELLE']);
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['brand']);
            $table->dropColumn('brand');
        });
    }
};
