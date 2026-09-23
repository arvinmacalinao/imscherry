<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    @include('orders.partials.invoice-styles')
</head>
<body>
    @include('orders.partials.invoice-page', ['order' => $order])
</body>
</html>
