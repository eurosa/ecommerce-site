<?php
require_once 'includes/config.php';

$message_sent = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // Here you would typically send an email or save to database
    // For now, we'll just show a success message
    $message_sent = true;
}
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <h1 class="page-title">Contact Us</h1>
    
    <div class="contact-container">
        <div class="contact-info-side">
            <h2>Get in Touch</h2>
            <p>Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
            
            <div class="contact-details">
                <div class="contact-detail">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h3>Address</h3>
                        <p><?php echo $company['company_address']; ?></p>
                    </div>
                </div>
                
                <div class="contact-detail">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h3>Phone</h3>
                        <p><?php echo $company['company_phone']; ?></p>
                    </div>
                </div>
                
                <div class="contact-detail">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h3>Email</h3>
                        <p><?php echo $company['company_email']; ?></p>
                    </div>
                </div>
                
                <div class="contact-detail">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h3>Business Hours</h3>
                        <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                        <p>Saturday: 10:00 AM - 4:00 PM</p>
                        <p>Sunday: Closed</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="contact-form-side">
            <?php if ($message_sent): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <h3>Thank You!</h3>
                <p>Your message has been sent. We'll get back to you soon.</p>
            </div>
            <?php endif; ?>
            
            <form action="contact.php" method="POST" class="contact-form">
                <div class="form-group">
                    <label for="name">Your Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Your Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject *</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php';