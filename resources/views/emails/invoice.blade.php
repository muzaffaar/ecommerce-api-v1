<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
</head>
<body>
    <h1>Invoice for Your Order</h1>
    <p>Dear {{ $order->user->name }},</p>
    <p>Thank you for your order. Attached is the invoice for your recent purchase.</p>
    <p>Order ID: {{ $order->order_number }}</p>
    <p>Order Date: {{ $order->created_at->format('F d, Y') }}</p>
    <p>Total Amount: ${{ number_format($order->total_amount, 2) }}</p>
    <p>If you have any questions, feel free to contact our support team.</p>
    <p>Thank you,</p>
    <p>Your Company Name</p>
</body>
</html>
