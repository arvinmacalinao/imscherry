<table>
    <thead>
        <tr>
            <th>Order Number</th>
            <th>Tracking Number</th>
            <th>Date Order</th>
            <th>Customer Name</th>
            <th>Product</th>
            <th>SKU</th>
            <th>Quantity</th>
        </tr>
    </thead>

    <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row['order_number'] }}</td>
                <td>{{ $row['tracking_number'] }}</td>
                <td>{{ $row['date_order'] }}</td>
                <td>{{ $row['customer_name'] }}</td>
                <td>{{ $row['product_name'] }}</td>
                <td>{{ $row['sku'] }}</td>
                <td>{{ $row['quantity'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
