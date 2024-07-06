<!-- resources/views/emails/order_confirmation.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .container {
            margin: 0 auto;
            padding: 20px;
            max-width: 600px;
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
        .footer {
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Confirmation</h1>
        </div>
        <div class="order-details">
            <h2>Order Details</h2>
            <p><strong>Order ID:</strong> {{ $order->order_number }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('F d, Y') }}</p>
            <p><strong>Customer:</strong> {{ $order->user->name }}</p>
            <p><strong>Email:</strong> {{ $order->user->email }}</p>
            <p><strong>Shipping Address:</strong> {{ $order->user->address }}</p>
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
        <div class="footer">
            <p>Thank you for your order!</p>
            <p>If you have any questions, feel free to contact our support team.</p>
        </div>
    </div>
</body>
</html>
