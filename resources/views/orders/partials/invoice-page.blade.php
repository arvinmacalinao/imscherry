{{--
    One form = two copies (Customer Copy on top, Accounting Copy below) on the pre-printed form.

    Every field is placed at a fixed position in millimetres measured from the top-left corner
    of the pre-printed form, so long names/addresses can never push other fields out of place.

    CALIBRATION:
      - Paper size and a global shift for EVERY field live in config/invoice.php.
      - If only ONE field is off, change that field's value in $f below.
      - $copyGap is the distance between the top copy and the bottom copy.
--}}
@php
    $offsetX = config('invoice.offset_x_mm', 0);
    $offsetY = config('invoice.offset_y_mm', 0);
    $copyGap = 139.8;   // mm, top of Customer Copy -> top of Accounting Copy
    $maxRows = 10;      // item lines that fit inside the items box

    // [left, top] in mm, relative to the top of one copy
    $f = [
        'invoice_no'     => ['left'  => 163,  'top' => 14.4],
        'tracking_label' => ['left'  => 130,  'top' => 20.8],
        'tracking'       => ['left'  => 158,  'top' => 20.8],
        'customer'       => ['left'  => 44.5, 'top' => 27.1],
        'order_id'       => ['left'  => 160,  'top' => 27.1],
        'address'        => ['left'  => 44.5, 'top' => 32.6, 'width' => 95],
        'order_date'     => ['left'  => 160,  'top' => 32.9],
        'items'          => ['left'  => 8,    'top' => 47.5],
        'grand_total'    => ['left'  => 165,  'top' => 106.6, 'width' => 32],
        'prepared_by'    => ['left'  => 13.5, 'top' => 122.5, 'width' => 55],
    ];

    $pos = function (string $key, float $copyTop) use ($f, $offsetX, $offsetY) {
        $p = $f[$key];
        $css = 'top:' . ($p['top'] + $copyTop + $offsetY) . 'mm;';
        $css .= 'left:' . ($p['left'] + $offsetX) . 'mm;';
        if (isset($p['width'])) {
            $css .= 'width:' . $p['width'] . 'mm;';
        }
        return $css;
    };

    $grandTotal = $order->details->sum(fn ($item) => $item->quantity * ($item->unit_price ?? 0));
    $preparedBy = auth()->user()->name ?? '';
@endphp

<div class="invoice-page{{ ($last ?? true) ? '' : ' page-break' }}">
    @foreach ([0, $copyGap] as $copyTop)
        <div class="field" style="{{ $pos('invoice_no', $copyTop) }}">{{ $order->invoice_no }}</div>

        <div class="field" style="{{ $pos('tracking_label', $copyTop) }}">Tracking No.</div>
        <div class="field" style="{{ $pos('tracking', $copyTop) }}">{{ $order->tracking_number ?: '-' }}</div>

        <div class="field" style="{{ $pos('customer', $copyTop) }}">{{ $order->customer_name }}</div>
        <div class="field small" style="{{ $pos('order_id', $copyTop) }}">{{ $order->order_number }}</div>

        <div class="field small address" style="{{ $pos('address', $copyTop) }}">{{ $order->shipping_address }}</div>
        <div class="field" style="{{ $pos('order_date', $copyTop) }}">{{ $order->order_date?->format('F j, Y') }}</div>

        <table class="field items" style="{{ $pos('items', $copyTop) }}">
            @foreach ($order->details->take($maxRows) as $item)
                <tr>
                    <td class="col-qty">{{ $item->quantity }}</td>
                    <td class="col-desc">{{ $item->product->name ?? $item->product_name }}</td>
                    <td class="col-price">{{ number_format($item->unit_price ?? 0, 2) }}</td>
                    <td class="col-total">{{ number_format($item->quantity * ($item->unit_price ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </table>

        <div class="field text-right" style="{{ $pos('grand_total', $copyTop) }}">{{ number_format($grandTotal, 2) }}</div>

        <div class="field text-center" style="{{ $pos('prepared_by', $copyTop) }}">{{ $preparedBy }}</div>
    @endforeach
</div>
