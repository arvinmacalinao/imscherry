{{--
    One form = two copies (Customer Copy on top, Accounting Copy below) on the pre-printed form.
    Positions fit both the Pure Living ("DR No.") and Cherry ("SR No.") forms; they differ by 1-2mm.

    Every field is placed at a fixed position in millimetres measured from the top-left corner
    of the pre-printed form (left edge = the perforation of the left tractor-hole strip),
    so long names/addresses can never push other fields out of place.

    CALIBRATION:
      - Paper size and a global shift for EVERY field live in config/invoice.php.
      - If only ONE field is off, change that field's value in $f below.
      - $copyGap is the distance between the top copy and the bottom copy.
--}}
@php
    $offsetX = config('invoice.offset_x_mm', 0);
    $offsetY = config('invoice.offset_y_mm', 0);
    $copyGap = 140.0;   // mm, top of Customer Copy -> top of Accounting Copy
    $maxRows = 10;      // item lines that fit inside the items box
    $descChars = 52;    // 9pt mono characters that fit in the description column

    // too many items: show one line less and say how many are not listed (grand total still counts all)
    $shownItems = $order->details->count() > $maxRows ? $order->details->take($maxRows - 1) : $order->details;
    $hiddenCount = $order->details->count() - $shownItems->count();

    // [left, top] in mm, relative to the top of one copy
    $f = [
        'invoice_no'     => ['left'  => 163,  'top' => 5.9],     // right of "DR No.:" / "SR No.:"
        'tracking_label' => ['left'  => 138,  'top' => 15.5],    // clear of the "30 DAYS WARRANTY" stamp
        'tracking'       => ['left'  => 164,  'top' => 15.5],
        'customer'       => ['left'  => 26,   'top' => 28.2],    // right of "Customer:"
        'order_id'       => ['left'  => 163,  'top' => 26.4],    // right of "Order ID:"
        'address'        => ['left'  => 26,   'top' => 33.6, 'width' => 110],  // right of "Ship to:"
        'order_date'     => ['left'  => 163,  'top' => 31.6],    // right of "Order date:"
        'items'          => ['left'  => 5.5,  'top' => 47.8],    // just under the table header
        'grand_total'    => ['left'  => 172,  'top' => 106.3, 'width' => 32],  // on the GRAND TOTAL line
        'prepared_by'    => ['left'  => 13.7, 'top' => 123.1, 'width' => 54.8], // above the signature line
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
            @foreach ($shownItems as $item)
                <tr>
                    <td class="col-qty">{{ $item->quantity }}</td>
                    <td class="col-desc">{{ Str::limit($item->product->name ?? $item->product_name, $descChars, '..') }}</td>
                    <td class="col-price">{{ number_format($item->unit_price ?? 0, 2) }}</td>
                    <td class="col-total">{{ number_format($item->quantity * ($item->unit_price ?? 0), 2) }}</td>
                </tr>
            @endforeach
            @if ($hiddenCount > 0)
                <tr>
                    <td class="col-qty"></td>
                    <td class="col-desc">+ {{ $hiddenCount }} more item(s) - see order {{ $order->order_number }}</td>
                    <td class="col-price"></td>
                    <td class="col-total"></td>
                </tr>
            @endif
        </table>

        <div class="field text-right" style="{{ $pos('grand_total', $copyTop) }}">{{ number_format($grandTotal, 2) }}</div>

        <div class="field text-center" style="{{ $pos('prepared_by', $copyTop) }}">{{ $preparedBy }}</div>
    @endforeach
</div>
