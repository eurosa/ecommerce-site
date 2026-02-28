<?php
require_once 'includes/config.php';
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <h1 class="page-title">About Us</h1>
    
    <div class="about-content">
        <div class="about-section">
            <h2><?php echo $company['company_name']; ?></h2>
            <p><?php echo $company['company_about']; ?></p>
        </div>
        
        <div class="about-section">
            <h2>Our Mission</h2>
            <p>To provide high-quality products at affordable prices while ensuring excellent customer service and satisfaction.</p>
        </div>
        
        <div class="about-section">
            <h2>Why Choose Us?</h2>
            <ul class="benefits-list">
                <li><i class="fas fa-check"></i> Quality products guaranteed</li>
                <li><i class="fas fa-check"></i> Competitive prices</li>
                <li><i class="fas fa-check"></i> Fast shipping</li>
                <li><i class="fas fa-check"></i> 24/7 customer support</li>
                <li><i class="fas fa-check"></i> Secure payments</li>
                <li><i class="fas fa-check"></i> Easy returns</li>
            </ul>
        </div>
        
        <div class="about-section">
            <h2>Contact Information</h2>
            <div class="contact-info">
                <p><i class="fas fa-phone"></i> <?php echo $company['company_phone']; ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo $company['company_email']; ?></p>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo $company['company_address']; ?></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php';