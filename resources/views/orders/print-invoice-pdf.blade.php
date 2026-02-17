<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoices</title>
</head>
<style>
        @page { 
            margin-top: 10; 
            margin-left: 15; 
            margin-right: 15; 
            margin-bottom: 15; 
            size: letter portrait; 
        }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #000; }
        .invoice:not(:last-child) { page-break-after: always; width: 100%; margin-bottom: 20px; }
        .border { border: 0px solid #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .mb-1 { margin-bottom: 5px; }
        .mb-5 { margin-bottom: 20px; }
        .padding-left-30 { padding-left: 30px; }
        table { border-collapse: collapse; width: 100%; }
        td { vertical-align: top; padding: 3px; }
        .product-row td { border: 0px solid #000; }
</style>
<body>
    @foreach($orders as $order)
        <div class="invoice">
        {{-- ***** start invoice content ***** --}}
        {{-- Header --}}
        <table width="100%" style="margin-bottom: 20px;">
            <tr>
                <td width="99%" class="text-right"><strong>{{ $order->invoice_no }}</strong></td>
                <td width="1%">&nbsp;</td>
            </tr>
        </table>
        {{-- Tracking / Customer Info --}}
        <table width="100%" class="mb-1">
            <tr>
                <td width="10%" class="border">&nbsp;</td>
                <td width="40%" class="border">&nbsp;</td>
                <td width="25%" class="border text-right">Tracking No.</td>
                <td width="20%" class="border text-right">{{ $order->tracking_number ?? '-' }}</td>
                <td width="5%" class="border">&nbsp;</td>
            </tr>
        </table>

        <table width="100%" class="mb-1">
            <tr>
                <td width="10%" class="border">&nbsp;</td>
                <td width="40%" class="border padding-left-30">{{ $order->customer_name }}</td>
                <td width="45%" class="border text-right">{{ $order->order_number }}</td>
                <td width="5%" class="border">&nbsp;</td>
            </tr>
        </table>

        <table width="100%" class="mb-5">
            <tr>
                <td width="10%" class="border">&nbsp;</td>
                <td width="55%" class="border padding-left-30"><small>{{ $order->shipping_address }}</small></td>
                <td width="10%" class="border">&nbsp;</td>
                <td width="20%" class="border text-right">{{ $order->order_date->format('F j, Y') }}</td>
                <td width="5%" class="border">&nbsp;</td>
            </tr>
        </table>

        {{-- Items --}}
        @php 
            $grandTotal = 0; 
            $name = Auth::user()->name;
        @endphp
        <table width="100%" style="margin-bottom: 10px;">
            @foreach ($order->details as $item)
                @php $grandTotal += $item->quantity * $item->product->price; @endphp
                <tr class="product-row">
                    <td width="10%" class="text-center">{{ $item->quantity }}</td>
                    <td width="50%">{{ $item->product->name }}</td>
                    <td width="20%" class="text-right">{{ number_format($item->product->price, 2) }}</td>
                    <td width="20%" class="text-right">{{ number_format($item->quantity * $item->product->price, 2) }}</td>
                </tr>
            @endforeach

            {{-- pad empty rows --}}
            @for ($i = count($order->details); $i < 9; $i++)
                <tr class="product-row">
                    <td width="10%">&nbsp;</td>
                    <td width="50%">&nbsp;</td>
                    <td width="20%">&nbsp;</td>
                    <td width="20%">&nbsp;</td>
                </tr>
            @endfor
        </table>

        {{-- Totals --}}
        <table width="100%" class="mb-1">
            <tr>
                <td width="10%" class="border">&nbsp;</td>
                <td width="50%" class="border">&nbsp;</td>
                <td width="35%" class="border text-right"><strong>{{ number_format($grandTotal, 2) }}</strong></td>
                <td width="5%" class="border">&nbsp;</td>
            </tr>
        </table>

        <table width="100%" class="mb-1">
            <tr>
                <td width="100%" class="border">&nbsp;</td>
            </tr>
        </table>

        <table width="100%" class="mb-5">
            <tr>
                <td width="10%" class="border">&nbsp;</td>
                <td width="10%" class="border">&nbsp;</td>
                <td width="40%" class="border"><small>{{ $name }}</small></td>
                <td width="35%" class="border">&nbsp;</td>
                <td width="5%" class="border">&nbsp;</td>
            </tr>
        </table>

        {{-- ***** duplicate copy ***** --}}
        <table width="100%" style="margin-bottom: 20px;">
            <tr>
                <td width="90%" class="text-right"><strong>{{ $order->invoice_no }}</strong></td>
                <td width="10%">&nbsp;</td>
            </tr>
        </table>

        <table width="100%" class="mb-1">
            <tr>
                <td width="10%" class="border">&nbsp;</td>
                <td width="40%" class="border">&nbsp;</td>
                <td width="25%" class="border text-right">Tracking No.</td>
                <td width="20%" class="border text-right">{{ $order->tracking_number }}</td>
                <td width="5%" class="border">&nbsp;</td>
            </tr>
        </table>

        <table width="100%" class="mb-1">
            <tr>
                <td width="10%" class="border">&nbsp;</td>
                <td width="40%" class="border padding-left-30">{{ $order->customer_name }}</td>
                <td width="45%" class="border text-right">{{ $order->order_number }}</td>
                <td width="5%" class="border">&nbsp;</td>
            </tr>
        </table>

        <table width="100%" class="mb-5">
            <tr>
                <td width="10%" class="border">&nbsp;</td>
                <td width="55%" class="border padding-left-30"><small>{{ $order->shipping_address }}</small></td>
                <td width="10%" class="border">&nbsp;</td>
                <td width="20%" class="border text-right">{{ $order->order_date->format('F j, Y') }}</td>
                <td width="5%" class="border">&nbsp;</td>
            </tr>
        </table>

        @php $grandTotal = 0; @endphp
        <table width="100%" style="margin-bottom: 10px;">
            @foreach ($order->details as $item)
                @php $grandTotal += $item->quantity * $item->product->price; @endphp
                <tr class="product-row">
                    <td width="10%" class="text-center">{{ $item->quantity }}</td>
                    <td width="50%">{{ $item->product->name }}</td>
                    <td width="20%" class="text-right">{{ number_format($item->product->price, 2) }}</td>
                    <td width="20%" class="text-right">{{ number_format($item->quantity * $item->product->price, 2) }}</td>
                </tr>
            @endforeach

            {{-- pad empty rows --}}
            @for ($i = count($order->details); $i < 9; $i++)
                <tr class="product-row">
                    <td width="10%">&nbsp;</td>
                    <td width="50%">&nbsp;</td>
                    <td width="20%">&nbsp;</td>
                    <td width="20%">&nbsp;</td>
                </tr>
            @endfor
        </table>

        <table width="100%" class="mb-1">
            <tr>
                <td width="10%" class="border">&nbsp;</td>
                <td width="50%" class="border">&nbsp;</td>
                <td width="35%" class="border text-right"><strong>{{ number_format($grandTotal, 2) }}</strong></td>
                <td width="5%" class="border">&nbsp;</td>
            </tr>
        </table>

        <table width="100%" class="mb-1">
            <tr>
                <td width="100%" class="border">&nbsp;</td>
            </tr>
        </table>

        <table width="100%" class="mb-1">
            <tr>
                <td width="10%" class="border">&nbsp;</td>
                <td width="10%" class="border">&nbsp;</td>
                <td width="40%" class="border"><small>{{ $name }}</small></td>
                <td width="35%" class="border">&nbsp;</td>
                <td width="5%" class="border">&nbsp;</td>
            </tr>
        </table>
    </div>
    @endforeach
</body>
</html>
