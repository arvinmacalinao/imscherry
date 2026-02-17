<table>
    <thead>
        <tr>
            <th>Product Name</th>
            <th>Product Code</th>
            <th>Total Quantity</th>
            <th>Orders</th>
            <th>Customer</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
            <tr>
                <td>{{ $item['product_name'] }}</td>
                <td>{{ $item['product_code'] }}</td>
                <td>{{ $item['total_quantity'] }}</td>
                <td>{{ implode(', ', $item['orders']) }}</td>
                <td>{{ implode(', ', $item['order_names']) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
