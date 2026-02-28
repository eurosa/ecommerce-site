<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = getProduct($conn, $product_id);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Get all images for this product
$product_images = getProductImages($conn, $product_id);
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="product-details">
        <div class="product-gallery">
            <!-- Main Image Display -->
            <div class="main-image">
                <?php if (!empty($product_images)): ?>
                    <img id="mainProductImage" src="uploads/<?php echo $product_images[0]['image_path']; ?>" 
                         alt="<?php echo $product['name']; ?>">
                <?php else: ?>
                    <img src="uploads/placeholder.jpg" alt="No image available">
                <?php endif; ?>
            </div>
            
            <!-- Thumbnail Gallery -->
            <?php if (count($product_images) > 1): ?>
            <div class="thumbnail-gallery">
                <?php foreach ($product_images as $index => $image): ?>
                <div class="thumbnail <?php echo $index == 0 ? 'active' : ''; ?>" 
                     onclick="changeImage('<?php echo $image['image_path']; ?>', this)">
                    <img src="uploads/<?php echo $image['image_path']; ?>" 
                         alt="<?php echo $product['name'] . ' - Image ' . ($index + 1); ?>">
                    <?php if ($image['is_primary']): ?>
                    <span class="primary-badge-small"><i class="fas fa-star"></i></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="product-details-info">
            <h1><?php echo $product['name']; ?></h1>
            
            <?php if (count($product_images) > 1): ?>
            <div class="image-count">
                <i class="fas fa-images"></i> <?php echo count($product_images); ?> images
            </div>
            <?php endif; ?>
            
            <div class="product-details-price"><?php echo formatMoney($product['price']); ?></div>
            
            <div class="product-details-stock">
                <?php if($product['stock'] > 0): ?>
                <span class="in-stock">✓ In Stock (<?php echo $product['stock']; ?> available)</span>
                <?php else: ?>
                <span class="out-of-stock">✗ Out of Stock</span>
                <?php endif; ?>
            </div>
            
            <div class="product-details-description">
                <h3>Description</h3>
                <p><?php echo nl2br($product['description']); ?></p>
            </div>
            
            <?php if($product['stock'] > 0): ?>
            <form action="cart.php" method="POST" class="add-to-cart-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <div class="quantity-selector">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                </div>
                
                <button type="submit" class="btn btn-primary btn-large">Add to Cart</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.product-gallery {
    position: sticky;
    top: 100px;
}

.main-image {
    width: 100%;
    height: 400px;
    border: 1px solid #eee;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 20px;
    background: #f9f9f9;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.thumbnail-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 10px;
}

.thumbnail {
    width: 80px;
    height: 80px;
    border: 2px solid transparent;
    border-radius: 5px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}

.thumbnail:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.thumbnail.active {
    border-color: #ff6b6b;
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.primary-badge-small {
    position: absolute;
    top: 2px;
    right: 2px;
    background: #ff6b6b;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
}

.image-count {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.image-count i {
    color: #ff6b6b;
}

/* Lightbox styles (optional) */
.lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.lightbox.active {
    display: flex;
}

.lightbox img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
}

.lightbox-close {
    position: absolute;
    top: 20px;
    right: 20px;
    color: white;
    font-size: 2rem;
    cursor: pointer;
}

.lightbox-prev,
.lightbox-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    color: white;
    font-size: 2rem;
    cursor: pointer;
    padding: 20px;
}

.lightbox-prev {
    left: 20px;
}

.lightbox-next {
    right: 20px;
}

@media (max-width: 768px) {
    .product-gallery {
        position: static;
    }
    
    .main-image {
        height: 300px;
    }
    
    .thumbnail {
        width: 60px;
        height: 60px;
    }
}
</style>

<script>
function changeImage(imagePath, element) {
    // Update main image
    document.getElementById('mainProductImage').src = 'uploads/' + imagePath;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    element.classList.add('active');
}

// Optional: Add lightbox functionality
document.querySelector('.main-image').addEventListener('click', function() {
    const images = <?php echo json_encode(array_column($product_images, 'image_path')); ?>;
    openLightbox(images, 0);
});

function openLightbox(images, index) {
    // Create lightbox if it doesn't exist
    let lightbox = document.querySelector('.lightbox');
    
    if (!lightbox) {
        lightbox = document.createElement('div');
        lightbox.className = 'lightbox';
        lightbox.innerHTML = `
            <span class="lightbox-close">&times;</span>
            <span class="lightbox-prev">&#10094;</span>
            <span class="lightbox-next">&#10095;</span>
            <img src="" alt="Lightbox image">
        `;
        document.body.appendChild(lightbox);
        
        // Add event listeners
        lightbox.querySelector('.lightbox-close').onclick = () => lightbox.classList.remove('active');
        lightbox.querySelector('.lightbox-prev').onclick = () => navigateLightbox(-1);
        lightbox.querySelector('.lightbox-next').onclick = () => navigateLightbox(1);
    }
    
    lightbox.dataset.images = JSON.stringify(images);
    lightbox.dataset.currentIndex = index;
    lightbox.querySelector('img').src = 'uploads/' + images[index];
    lightbox.classList.add('active');
}

function navigateLightbox(direction) {
    const lightbox = document.querySelector('.lightbox');
    const images = JSON.parse(lightbox.dataset.images);
    let currentIndex = parseInt(lightbox.dataset.currentIndex);
    
    currentIndex = (currentIndex + direction + images.length) % images.length;
    lightbox.dataset.currentIndex = currentIndex;
    lightbox.querySelector('img').src = 'uploads/' + images[currentIndex];
}
</script>

<?php include 'includes/footer.php'; ?>