<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The app used to record its own times (imports, status changes, scans, stock moves...) in UTC,
     * 8 hours behind the Philippines, so e.g. an order imported at 7 AM showed the previous day.
     * config/app.php now uses Asia/Manila; this moves the times recorded before the switch forward
     * by 8 hours so old and new data line up.
     *
     * Not shifted:
     *  - dates copied from the platforms' files, which are already Philippine time
     *  - columns filled by the database's own CURRENT_TIMESTAMP default (failed_jobs.failed_at),
     *    which never used the app's timezone
     */
    private const PLATFORM_TIMES = [
        'orders.order_date',
        'orders.platform_created_at',
        'orders.platform_updated_at',
    ];

    private const DATABASE_CLOCK = [
        'failed_jobs.failed_at',
    ];

    public function up(): void
    {
        // order history's acted_at was left to the database default (the database server's timezone,
        // not UTC); it is the moment the log was written, so take it from created_at before shifting
        DB::statement('UPDATE order_status_logs SET acted_at = created_at WHERE created_at IS NOT NULL');

        $this->shift('+');
    }

    public function down(): void
    {
        $this->shift('-');
    }

    private function shift(string $sign): void
    {
        $columns = collect(DB::select(
            "SELECT table_name AS t, column_name AS c FROM information_schema.columns
             WHERE table_schema = DATABASE() AND data_type IN ('timestamp', 'datetime')"
        ))
            ->reject(fn ($col) => in_array("{$col->t}.{$col->c}", [...self::PLATFORM_TIMES, ...self::DATABASE_CLOCK]))
            ->groupBy('t');

        foreach ($columns as $table => $cols) {
            // all of a table's columns in one statement, each moved once
            $set = $cols->map(fn ($col) => "`{$col->c}` = `{$col->c}` {$sign} INTERVAL 8 HOUR")->implode(', ');

            DB::statement("UPDATE `{$table}` SET {$set}");
        }
    }
};
