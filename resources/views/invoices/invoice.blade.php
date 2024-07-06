<!-- resources/views/invoices/invoice.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            max-width: 800px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .order-details {
            margin-bottom: 20px;
        }
        .order-details h2 {
            margin: 0;
            color: #333;
        }
        .order-details p {
            margin: 5px 0;
            color: #666;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .items-table th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice</h1>
        </div>
        <div class="order-details">
            <h2>Order Details</h2>
            <p><strong>Order ID:</strong> {{ $order->order_number }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('F d, Y') }}</p>
            <p><strong>Customer:</strong> {{ $order->user->name }}</p>
            <p><strong>Email:</strong> {{ $order->user->email }}</p>
        </div>
        <div class="items">
            <h2>Items Purchased</h2>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        @php
                        $price = $item->product->price;
                        $subtotal = $item->price * $item->quantity;
                        if (!empty($item->attributes)) {
                            $item->attributes = json_decode($item->attributes, true);
                            if (isset($item->attributes[0]['price'])) {
                                $price = $item->attributes[0]['price'];
                                $subtotal = $item->attributes[0]['price'] * $item->quantity;
                            }else{
                                Log::error($item->attributes[0]);
                            }
                        }
                        @endphp
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($price, 2) }}</td>
                        <td>${{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="total"><strong>Total:</strong> ${{ number_format($order->total_amount, 2) }}</p>
        </div>
    </div>
</body>
</html>
