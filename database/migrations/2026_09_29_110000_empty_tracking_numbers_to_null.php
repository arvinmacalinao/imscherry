<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Importers saved a missing tracking number as '' instead of NULL, which hid the
     * "Add Tracking" button on those orders. Order::setTrackingNumberAttribute now stores
     * NULL; this fixes the orders imported before.
     */
    public function up(): void
    {
        DB::table('orders')->whereRaw("TRIM(tracking_number) = ''")->update(['tracking_number' => null]);
    }

    public function down(): void
    {
        // nothing to undo: NULL and '' both mean "no tracking number"
    }
};
