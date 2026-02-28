<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Require login
requireLogin();

$user = getCurrentUser($conn);
$orders = getUserOrders($conn, $user['id']);
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <h1 class="page-title">My Orders</h1>
    
    <?php if (mysqli_num_rows($orders) > 0): ?>
    <div class="orders-list">
        <?php while($order = mysqli_fetch_assoc($orders)): ?>
        <div class="order-card">
            <div class="order-header">
                <div class="order-info">
                    <h3>Order #<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></h3>
                    <p class="order-date"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                </div>
                <div class="order-status">
                    <span class="status-badge status-<?php echo $order['order_status']; ?>">
                        <?php echo ucfirst($order['order_status']); ?>
                    </span>
                </div>
            </div>
            
            <div class="order-body">
                <div class="order-total">
                    <strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?>
                </div>
                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">
                    View Details <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-orders">
        <i class="fas fa-shopping-bag"></i>
        <h2>No orders yet</h2>
        <p>Looks like you haven't placed any orders yet.</p>
        <a href="products.php" class="btn btn-primary">Start Shopping</a>
    </div>
    <?php endif; ?>
</div>

<style>
.orders-list {
    max-width: 800px;
    margin: 40px auto;
}

.order-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
}

.order-header {
    background: #f8f9fa;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
}

.order-info h3 {
    margin: 0 0 5px 0;
    color: #333;
}

.order-date {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.order-body {
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-total {
    font-size: 1.2rem;
    color: #ff6b6b;
}

.empty-orders {
    text-align: center;
    padding: 60px 20px;
}

.empty-orders i {
    font-size: 5rem;
    color: #ccc;
    margin-bottom: 20px;
}

.empty-orders h2 {
    margin-bottom: 10px;
    color: #333;
}

.empty-orders p {
    color: #666;
    margin-bottom: 30px;
}
</style>

<?php include 'includes/footer.php';