<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Require login
requireLogin();

$user = getCurrentUser($conn);
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$order = getOrderDetails($conn, $order_id, $user['id']);

if (!$order) {
    header('Location: profile.php');
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="order-details">
        <div class="order-header">
            <h1>Order Details</h1>
            <p class="order-number">Order #<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></p>
            <p class="order-date">Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['order_date'])); ?></p>
            <p class="order-status status-<?php echo $order['order_status']; ?>">
                Status: <?php echo ucfirst($order['order_status']); ?>
            </p>
        </div>
        
        <div class="order-info-grid">
            <div class="info-card">
                <h3>Shipping Information</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                <p><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>
            </div>
            
            <div class="info-card">
                <h3>Order Summary</h3>
                <p><strong>Subtotal:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                <p><strong>Shipping:</strong> Free</p>
                <p><strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
            </div>
        </div>
        
        <div class="order-items">
            <h3>Order Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    mysqli_data_seek($order['items'], 0);
                    while($item = mysqli_fetch_assoc($order['items'])): 
                    ?>
                    <tr>
                        <td>
                            <div class="item-info">
                                <div>
                                    <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                </div>
                            </div>
                        </td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total:</strong></td>
                        <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="order-actions">
            <a href="profile.php#orders" class="btn btn-secondary">Back to Orders</a>
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    </div>
</div>

<style>
.order-details {
    max-width: 1000px;
    margin: 40px auto;
}

.order-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #eee;
}

.order-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
    margin: 10px 0;
}

.order-date {
    color: #666;
    margin-bottom: 10px;
}

.order-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

.info-card {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 5px;
}

.info-card h3 {
    margin-bottom: 15px;
    color: #333;
}

.info-card p {
    margin-bottom: 8px;
    line-height: 1.6;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.items-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
}

.items-table td {
    padding: 15px;
    border-bottom: 1px solid #ddd;
}

.items-table tfoot td {
    padding: 15px;
    font-size: 1.1rem;
}

.text-right {
    text-align: right;
}

.order-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 40px;
}

@media (max-width: 768px) {
    .order-info-grid {
        grid-template-columns: 1fr;
    }
    
    .items-table {
        font-size: 0.9rem;
    }
}
</style>

<?php include 'includes/footer.php';