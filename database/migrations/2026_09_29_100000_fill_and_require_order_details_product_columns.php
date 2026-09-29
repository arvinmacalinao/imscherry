<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The order importers saved the product name under a wrong key, so order_details.product_name
     * was empty on every imported line, and the column had been made nullable by hand to let that
     * through. The importers are fixed; this fills the missing names from the product records and
     * puts back the NOT NULL constraints of create_order_details_table (product_name, sku, product_id).
     *
     * Plain SQL because doctrine/dbal is not installed (needed for ->change() on Laravel 10).
     */
    public function up(): void
    {
        // products may be soft-deleted, so read the table directly (no deleted_at filter)
        DB::statement("
            UPDATE order_details d
            JOIN products p ON p.id = d.product_id
            SET d.product_name = p.name
            WHERE d.product_name IS NULL OR d.product_name = ''
        ");

        DB::statement("UPDATE order_details SET product_name = '' WHERE product_name IS NULL");
        DB::statement("UPDATE order_details SET sku = '' WHERE sku IS NULL");

        DB::statement('ALTER TABLE order_details
            MODIFY product_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            MODIFY sku VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL');

        // product_id is already NOT NULL where the table was never edited by hand (production).
        // MySQL cannot change it while the foreign key exists, so drop and re-create the key
        // with the same rule as create_order_details_table (cascade on delete).
        if ($this->isNullable('product_id')) {
            DB::statement('ALTER TABLE order_details DROP FOREIGN KEY order_details_product_id_foreign');
            DB::statement('ALTER TABLE order_details MODIFY product_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE order_details ADD CONSTRAINT order_details_product_id_foreign
                FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        }
    }

    private function isNullable(string $column): bool
    {
        return DB::selectOne(
            'SELECT is_nullable AS nullable FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            ['order_details', $column]
        )->nullable === 'YES';
    }

    public function down(): void
    {
        // back to how production had it: product_name nullable (the filled names are kept)
        DB::statement('ALTER TABLE order_details
            MODIFY product_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
    }
};
