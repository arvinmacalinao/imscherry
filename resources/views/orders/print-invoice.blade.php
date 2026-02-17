<!DOCTYPE html>
<html lang="en">
    <head>
        <title>
            {{ config('app.name') }}
        </title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">
        <!-- External CSS libraries -->
        <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/bootstrap.min.css') }}">
        <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/fonts/font-awesome/css/font-awesome.min.css') }}">
        <!-- Google fonts -->
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <!-- Custom Stylesheet -->
        <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/style.css') }}">
        <style>
            @page { 
                
               
            }
        </style>
    </head>
    <body>
        <div class="container">
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
            </div>
        <script src="{{ asset('assets/invoice/js/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/invoice/js/jspdf.min.js') }}"></script>
        <script src="{{ asset('assets/invoice/js/html2canvas.js') }}"></script>
        <script src="{{ asset('assets/invoice/js/app.js') }}"></script>
    </body>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // only run auto-download if ?download=1 is in URL
    if (window.location.search.includes("download=1")) {
        const dateNow = new Date().toLocaleDateString();

        var pdfWidth = 612;   // Letter size (8.5in x 11in)
        var pdfHeight = 792;
        var margin = 20;

        html2canvas(document.querySelector("#invoice_wrapper"), {
            allowTaint: true,
            scale: 2,
            useCORS: true
        }).then(function (canvas) {
            var imgData = canvas.toDataURL("image/jpeg", 1.0);
            var pdf = new jsPDF("p", "pt", [pdfWidth, pdfHeight]);

            var imgWidth = pdfWidth - margin * 2;
            var pageHeight = pdfHeight - margin * 2;
            var imgHeight = (canvas.height * imgWidth) / canvas.width;

            var heightLeft = imgHeight;
            var position = margin;

            pdf.addImage(imgData, "JPG", margin, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;

            while (heightLeft > 0) {
                position = heightLeft - imgHeight + margin;
                pdf.addPage([pdfWidth, pdfHeight]);
                pdf.addImage(imgData, "JPG", margin, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }

            // Save PDF then close window
            pdf.save(`invoice-${dateNow}.pdf`);
            
            // Give browser a moment to finish download, then close tab
            setTimeout(function () {
                window.close();
            }, 1000);
        });
    }
});
</script>

</html>
