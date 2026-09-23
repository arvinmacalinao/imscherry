<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoices</title>
    @include('orders.partials.invoice-styles')
</head>
<body>
    @foreach ($orders as $order)
        @include('orders.partials.invoice-page', ['order' => $order, 'last' => $loop->last])
    @endforeach
</body>
</html>
