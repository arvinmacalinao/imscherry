<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoices</title>
    <link href="{{ asset('assets/invoice/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/invoice/css/style.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .invoice { page-break-after: always; }
        .invoice:last-child { page-break-after: auto; }
    </style>
</head>
<body>
    @foreach($orders as $order)
        <div class="invoice">
            {{-- ***** start invoice content ***** --}}
        <div class="row">
                    <div class="col-lg-12">
                        <div class="invoice-inner-9" id="invoice_wrapper">
                            <div class="invoice-top">
                                <div class="row">
                                    <div class="col-md-11 mb-5" style="text-align: end">
                                        <br>
                                        <span><strong>{{ $order->invoice_no }}</strong></span>
                                        <br>
                                    </div>
                                    <div class="col-md-1" style="text-align: end">
                                    {{-- blank space --}}
                                    </div>
                                </div>
                                 <div class="row mb-1">
                                    {{-- 2nd roww --}}
                                    <div class="col-md-1 border" style="text-align: end">
                                    </div>
                                    <div class="col-md-5 border" style="text-align: start">
                                        <span></span>
                                    </div>
                                    <div class="col-md-3 border" style="text-align: end">
                                        <span>Tracking No. </span>
                                    </div>
                                    <div class="col-md-2 border" style="text-align: end">
                                        <span>{{ $order->tracking_number }}</span>
                                    </div>
                                    <div class="col-md-1 border" style="text-align: start">
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    {{-- 2nd roww --}}
                                    <div class="col-md-1 border" style="text-align: end">
                                    </div>
                                    <div class="col-md-5 border" style="text-align: start; padding-left: 30px;">
                                        <span style="">{{ $order->customer_name }}</span>
                                    </div>
                                    <div class="col-md-5 border" style="text-align: end">
                                        <span>{{ $order->order_number }}</span>
                                    </div>
                                    <div class="col-md-1 border" style="text-align: start">
                                    </div>
                                </div>
                                <div class="row mb-5">
                                    {{-- 2nd roww --}}
                                    <div class="col-md-1 border" style="text-align: end">
                                    </div>
                                    <div class="col-md-7 border" style="text-align: start; padding-left: 30px;">
                                        <small style="">{{ $order->shipping_address }}</small>
                                    </div>
                                    <div class="col-md-1 border" style="text-align: start">
                                    </div>
                                    <div class="col-md-2 border" style="text-align: end;">
                                        <span>{{ $order->order_date->format('F j, Y') }}</span>
                                    </div>
                                    <div class="col-md-1 border" style="text-align: start">
                                    </div>
                                </div>
                                 @php 
                                     $grandTotal = 0; 
                                     $name = Auth::user()->name;
                                 @endphp
                                <div class="products-area">
                                @foreach ($order->details as $item)
                                @php 
                                    $grandTotal += $item->quantity * $item->product->price; 
                                @endphp
                                <div class="row product-row">
                                    <div class="col-md-1 border text-center">
                                        {{ $item->quantity }}
                                    </div>
                                    <div class="col-md-7 border">
                                        {{ $item->product->name }}
                                    </div>
                                    <div class="col-md-2 border text-right">
                                        {{ number_format($item->product->price, 2) }}
                                    </div>
                                    <div class="col-md-2 border text-right">
                                        {{ number_format($item->quantity * $item->product->price, 2) }}
                                    </div>
                                </div>
                                @endforeach
                                {{-- pad with empty rows so it always fills the fixed area --}}
                                @for ($i = count($order->details); $i < 12; $i++)
                                    <div class="row product-row">
                                            <div class="col-md-6">&nbsp;</div>
                                            <div class="col-md-2">&nbsp;</div>
                                            <div class="col-md-2">&nbsp;</div>
                                            <div class="col-md-2">&nbsp;</div>
                                    </div>
                                @endfor
                                <div class="row">
                                    {{-- 2nd roww --}}
                                    <div class="col-md-1 border" style="text-align: end">
                                    </div>
                                    <div class="col-md-5 border" style="text-align: start">
                                        <small></small>
                                    </div>
                                    <div class="col-md-5 border" style="text-align: end">
                                        <span><strong>{{ number_format($grandTotal, 2) }}</strong></span>
                                    </div>
                                    <div class="col-md-1 border" style="text-align: start">
                                    </div>
                                </div>
                                <div class="row">
                                            {{-- 2nd roww --}}

                                            <div class="col-md-12 border" style="text-align: start">
                                                <span>&nbsp;</span>
                                            </div>

                                </div>
                                <div class="row mb-5">
                                    {{-- 2nd roww --}}
                                    <div class="col-md-1 border" style="text-align: end">
                                    </div>
                                    <div class="col-md-1 border" style="text-align: end">
                                    </div>
                                    <div class="col-md-4 border" style="text-align: start">
                                        <small>{{ $name }}</small>
                                    </div>
                                    <div class="col-md-5 border" style="text-align: end">
                                    
                                    </div>
                                    <div class="col-md-1 border" style="text-align: start">
                                    </div>
                                </div>
                                <p>  <br><br><br></p>
                                {{-- duplicate --}}
                                <div class="row">
                                    <div class="col-md-11 mb-5" style="text-align: end">
                                        <br>
                                        <span><strong>{{ $order->invoice_no }}</strong></span>
                                        <br>
                                    </div>
                                    <div class="col-md-1" style="text-align: end">
                                    {{-- blank space --}}
                                    </div>
                                </div>
                                 <div class="row mb-1">
                                    {{-- 2nd roww --}}
                                    <div class="col-md-1 border" style="text-align: end">
                                    </div>
                                    <div class="col-md-5 border" style="text-align: start">
                                        <span></span>
                                    </div>
                                    <div class="col-md-3 border" style="text-align: end">
                                        <span>Tracking No. </span>
                                    </div>
                                    <div class="col-md-2 border" style="text-align: end">
                                        <span>{{ $order->tracking_number }}</span>
                                    </div>
                                    <div class="col-md-1 border" style="text-align: start">
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    {{-- 2nd roww --}}
                                    <div class="col-md-1 border" style="text-align: end">
                                    </div>
                                    <div class="col-md-5 border" style="text-align: start; padding-left: 30px;">
                                        <span style="">{{ $order->customer_name }}</span>
                                    </div>
                                    <div class="col-md-5 border" style="text-align: end">
                                        <span>{{ $order->order_number }}</span>
                                    </div>
                                    <div class="col-md-1 border" style="text-align: start">
                                    </div>
                                </div>
                                <div class="row mb-5">
                                    {{-- 2nd roww --}}
                                    <div class="col-md-1 border" style="text-align: end">
                                    </div>
                                    <div class="col-md-7 border" style="text-align: start; padding-left: 30px;">
                                        <small style="">{{ $order->shipping_address }}</small>
                                    </div>
                                    <div class="col-md-1 border" style="text-align: start">
                                    </div>
                                    <div class="col-md-2 border" style="text-align: end;">
                                        <span>{{ $order->order_date->format('F j, Y') }}</span>
                                    </div>
                                    <div class="col-md-1 border" style="text-align: start">
                                    </div>
                                </div>
                                 @php 
                                     $grandTotal = 0; 
                                     $name = Auth::user()->name;
                                 @endphp
                                <div class="products-area">
                                @foreach ($order->details as $item)
                                @php 
                                    $grandTotal += $item->quantity * $item->product->price; 
                                @endphp
                                <div class="row product-row">
                                    <div class="col-md-1 border text-center">
                                        {{ $item->quantity }}
                                    </div>
                                    <div class="col-md-7 border">
                                        {{ $item->product->name }}
                                    </div>
                                    <div class="col-md-2 border text-right">
                                        {{ number_format($item->product->price, 2) }}
                                    </div>
                                    <div class="col-md-2 border text-right">
                                        {{ number_format($item->quantity * $item->product->price, 2) }}
                                    </div>
                                </div>
                                @endforeach
                                {{-- pad with empty rows so it always fills the fixed area --}}
                                @for ($i = count($order->details); $i < 11; $i++)
                                    <div class="row product-row">
                                            <div class="col-md-6">&nbsp;</div>
                                            <div class="col-md-2">&nbsp;</div>
                                            <div class="col-md-2">&nbsp;</div>
                                            <div class="col-md-2">&nbsp;</div>
                                    </div>
                                @endfor
                                <div class="row">
                                    {{-- 2nd roww --}}
                                    <div class="col-md-1 border" style="text-align: end">
                                    </div>
                                    <div class="col-md-5 border" style="text-align: start">
                                        <small></small>
                                    </div>
                                    <div class="col-md-5 border" style="text-align: end">
                                        <span><strong>{{ number_format($grandTotal, 2) }}</strong></span>
                                    </div>
                                    <div class="col-md-1 border" style="text-align: start">
                                    </div>
                                </div>
                                <div class="row">
                                            {{-- 2nd roww --}}
                                            <div class="col-md-12 border" style="text-align: start">
                                                <span>&nbsp;</span>
                                            </div>
                                </div>
                                <div class="row">
                                    {{-- 2nd roww --}}
                                    <div class="col-md-1 border" style="text-align: end">
                                    </div>
                                    <div class="col-md-1 border" style="text-align: end">
                                    </div>
                                    <div class="col-md-4 border" style="text-align: start">
                                        <small>{{ $name }}</small>
                                    </div>
                                    <div class="col-md-5 border" style="text-align: end">
                                    
                                    </div>
                                    <div class="col-md-1 border" style="text-align: start">
                                    </div>
                                </div>
                            </div>
                        </div>        
                    </div>
                </div>
        {{-- ***** end invoice content ***** --}}
            @include('orders.print-invoice2', ['order' => $order])
        </div>
    @endforeach
</body>
</html>
