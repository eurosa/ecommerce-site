<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php'; // Added for consistency

// Check for logout message
$logout_message = '';
if (isset($_GET['logged_out'])) {
    $logout_message = 'You have been successfully logged out.';
}

$products = getProducts($conn);
?>

<?php include 'includes/header.php'; ?>



<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Welcome to <?php echo $company['company_name']; ?></h1>
            <p>Discover amazing products at great prices</p>
            <a href="products.php" class="btn btn-primary">Shop Now</a>
        </div>
    </div>
</section>

<!-- Logout Message -->
<?php if ($logout_message): ?>
<div class="container">
    <div class="success-message" style="margin-top: 20px;">
        <?php echo $logout_message; ?>
    </div>
</div>
<?php endif; ?>

<!-- Featured Products -->
<section class="featured-products">
    <div class="container">
        <h2 class="section-title">Featured Products</h2>
        <div class="product-grid">
            <?php 
            $count = 0;
            while($product = mysqli_fetch_assoc($products)): 
                if($count >= 8) break;
                
                // Get primary image for this product
                $primary_image = getProductPrimaryImage($conn, $product['id']);
                
                // If no primary image found, try the old image field as fallback
                if (!$primary_image && !empty($product['image'])) {
                    $primary_image = $product['image'];
                }
                
                // Get total image count for badge
                $all_images = getProductImages($conn, $product['id']);
                $image_count = count($all_images);
            ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="uploads/<?php echo $primary_image ?: 'placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                    
                    <!-- Image count badge -->
                    <?php if($image_count > 1): ?>
                    <span class="image-count-badge">
                        <i class="fas fa-images"></i> <?php echo $image_count; ?>
                    </span>
                    <?php endif; ?>
                    
                    <!-- Stock badge -->
                    <?php if($product['stock'] < 10 && $product['stock'] > 0): ?>
                    <span class="stock-badge">Only <?php echo $product['stock']; ?> left</span>
                    <?php elseif($product['stock'] == 0): ?>
                    <span class="stock-badge out-of-stock">Out of Stock</span>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>...</p>
                    <div class="product-price"><?php echo formatMoney($product['price']); ?></div>
                    <div class="product-actions">
                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <?php if($product['stock'] > 0): ?>
                        <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </a>
                        <?php else: ?>
                        <button class="btn btn-disabled" disabled>
                            <i class="fas fa-times-circle"></i> Out of Stock
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php 
                $count++;
            endwhile; 
            
            // If no products found
            if ($count == 0):
            ?>
            <div class="no-products">
                <p>No products found. Check back soon!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <div class="features-grid">
            <div class="feature">
                <i class="fas fa-truck"></i>
                <h3>Free Shipping</h3>
                <p>On orders over $50</p>
            </div>
            <div class="feature">
                <i class="fas fa-undo"></i>
                <h3>30-Day Returns</h3>
                <p>Money-back guarantee</p>
            </div>
            <div class="feature">
                <i class="fas fa-lock"></i>
                <h3>Secure Payment</h3>
                <p>100% secure transactions</p>
            </div>
            <div class="feature">
                <i class="fas fa-headset"></i>
                <h3>24/7 Support</h3>
                <p>Always here to help</p>
            </div>
        </div>
    </div>
</section>

<style>
/* Additional styles for the badges */
.image-count-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 107, 107, 0.9);
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 5px;
}

.image-count-badge i {
    font-size: 0.7rem;
}

.stock-badge {
    position: absolute;
    bottom: 10px;
    left: 10px;
    background: #ff6b6b;
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
    z-index: 2;
}

.stock-badge.out-of-stock {
    background: #dc3545;
}

.product-image {
    position: relative;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.no-products {
    grid-column: 1 / -1;
    text-align: center;
    padding: 50px;
    color: #666;
}

.btn-disabled {
    background: #ccc;
    color: #666;
    cursor: not-allowed;
    pointer-events: none;
    opacity: 0.7;
}

.btn i {
    margin-right: 5px;
}

/* Animation for new products */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.product-card {
    animation: fadeIn 0.5s ease-out;
}
</style>

<?php include 'includes/footer.php'; ?>