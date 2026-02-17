<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Http\FormRequest;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class OrderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'order_date' => Carbon::now()->format('Y-m-d'),
            'order_number' => strtoupper(Str::random(12)),
            'status_id' => 1,
            'total_products' => Cart::instance('order')->count(),
            'total' => Cart::instance('order')->subtotal(),
            'invoice_no' => IdGenerator::generate([
                'table' => 'orders',
                'field' => 'invoice_no',
                'length' => 10,
                'prefix' => 'INV-',
            ]),
        ]);
    }
}
