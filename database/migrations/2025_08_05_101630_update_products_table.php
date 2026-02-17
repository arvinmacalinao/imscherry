<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Rename or standardize
            $table->renameColumn('code', 'sku');

            // Drop unneeded columns
            $table->dropColumn([
                'slug',
                'buying_price',
                'selling_price',
                'quantity_alert',
                'tax',
                'tax_type',
                'notes',
                'product_image',
            ]);

            // Add audit + soft deletes
            $table->unsignedBigInteger('created_by')->nullable()->after('category_id');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');

            // Foreign keys for audit trail
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();

            // Rename existing price columns to a single `price` column if needed
            if (!Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 15, 2)->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert back to old schema if needed
            $table->renameColumn('sku', 'code');

            $table->string('slug')->nullable();
            $table->integer('buying_price')->nullable();
            $table->integer('selling_price')->nullable();
            $table->integer('quantity_alert')->nullable();
            $table->integer('tax')->nullable();
            $table->tinyInteger('tax_type')->nullable();
            $table->text('notes')->nullable();
            $table->string('product_image')->nullable();
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by', 'deleted_at', 'price']);
        });
    }
};
