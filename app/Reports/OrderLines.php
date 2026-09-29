<?php

namespace App\Reports;

use App\Models\OrderDetails;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Order lines joined with their order, product, category, shop and platform, with the
 * filters every report shares. Reports add their own status condition and columns.
 *
 * Amounts use the price the platform sold the item for (order_details.unit_price).
 */
class OrderLines
{
    public const AMOUNT = 'order_details.quantity * COALESCE(order_details.unit_price, 0)';

    public const BRAND = "COALESCE(categories.brand, 'Other')";

    public static function query(array $f = [], string $dateColumn = 'orders.order_date'): Builder
    {
        return OrderDetails::query()
            ->setEagerLoads([]) // OrderDetails always eager-loads product; reports select what they need
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_details.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('shop_names', 'shop_names.id', '=', 'orders.shop_name_id')
            ->leftJoin('platforms', 'platforms.id', '=', 'orders.platform_id')
            ->whereNull('orders.deleted_at')
            ->when($f['date_from'] ?? null, fn ($q, $d) => $q->whereDate($dateColumn, '>=', $d))
            ->when($f['date_to'] ?? null, fn ($q, $d) => $q->whereDate($dateColumn, '<=', $d))
            ->when($f['shop_id'] ?? null, fn ($q, $id) => $q->where('orders.shop_name_id', $id))
            ->when($f['platform_id'] ?? null, fn ($q, $id) => $q->where('orders.platform_id', $id))
            ->when($f['category_id'] ?? null, fn ($q, $id) => $q->where('products.category_id', $id))
            ->when($f['product_id'] ?? null, fn ($q, $id) => $q->where('order_details.product_id', $id))
            ->when($f['brand'] ?? null, fn ($q, $brand) => $brand === 'Other'
                ? $q->whereNull('categories.brand')
                : $q->where('categories.brand', $brand))
            ->when(trim($f['search'] ?? ''), function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(fn ($w) => $w
                    ->where('orders.order_number', 'like', $like)
                    ->orWhere('orders.invoice_no', 'like', $like)
                    ->orWhere('orders.tracking_number', 'like', $like)
                    ->orWhere('orders.customer_name', 'like', $like)
                    ->orWhere('order_details.sku', 'like', $like)
                    ->orWhere('order_details.product_name', 'like', $like)
                    ->orWhere('products.name', 'like', $like));
            });
    }

    /** Columns every line listing shows */
    public static function columns(): array
    {
        return [
            'order_details.id',
            'order_details.order_id',
            'order_details.sku',
            'order_details.quantity',
            'order_details.unit_price',
            DB::raw(self::AMOUNT . ' AS line_total'),
            DB::raw('COALESCE(products.name, order_details.product_name) AS item_name'),
            DB::raw("COALESCE(categories.name, 'Uncategorized') AS category_name"),
            DB::raw(self::BRAND . ' AS brand'),
            'orders.order_number',
            'orders.invoice_no',
            'orders.order_date',
            'orders.payment_type',
            'orders.customer_name',
            'shop_names.name AS shop_name',
            'platforms.name AS platform_name',
        ];
    }
}
