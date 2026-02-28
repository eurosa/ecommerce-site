<?php
session_start();
require_once '../includes/config.php';

// Simple admin authentication
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Get statistics
$product_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$order_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders"))['total'];

$recent_orders = mysqli_query($conn, "SELECT * FROM orders ORDER BY order_date DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 250px; background: #333; color: white; padding: 20px; }
        .admin-sidebar a { color: white; display: block; padding: 10px; text-decoration: none; }
        .admin-sidebar a:hover { background: #444; }
        .admin-main { flex: 1; padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stat-card h3 { margin: 0 0 10px 0; color: #666; }
        .stat-card .number { font-size: 24px; font-weight: bold; color: #333; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <a href="index.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="add-product.php"><i class="fas fa-plus"></i> Add Product</a>
                <a href="view-orders.php"><i class="fas fa-shopping-cart"></i> View Orders</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
        
        <div class="admin-main">
            <h1>Dashboard</h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Products</h3>
                    <div class="number"><?php echo $product_count; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <div class="number"><?php echo $order_count; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <div class="number">$<?php echo number_format($revenue ?? 0, 2); ?></div>
                </div>
            </div>
            
            <h2>Recent Orders</h2>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                    <tr>
                        <td>#<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo $order['customer_name']; ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo $order['order_status']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>