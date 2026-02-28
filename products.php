<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$products = getProducts($conn);
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <h1 class="page-title">All Products</h1>
    
    <div class="product-grid">
        <?php while($product = mysqli_fetch_assoc($products)): ?>
        <div class="product-card">
            <div class="product-image">
                <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                <?php if($product['stock'] < 10): ?>
                <span class="stock-badge">Only <?php echo $product['stock']; ?> left</span>
                <?php endif; ?>
            </div>
            <div class="product-info">
                <h3><?php echo $product['name']; ?></h3>
                <p class="product-description"><?php echo substr($product['description'], 0, 50); ?>...</p>
                <div class="product-price"><?php echo formatMoney($product['price']); ?></div>
                <div class="product-actions">
                    <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">View Details</a>
                    <?php if($product['stock'] > 0): ?>
                    <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary">Add to Cart</a>
                    <?php else: ?>
                    <button class="btn btn-disabled" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'includes/footer.php'; 