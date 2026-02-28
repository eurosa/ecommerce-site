<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $result = generateResetToken($conn, $email);
    
    if ($result['success']) {
        // In production, send email with reset link
        $reset_link = SITE_URL . "reset-password.php?token=" . $result['token'];
        
        // For demo, show the link (in production, email it)
        $success = "Password reset link has been sent to your email.<br>
                    <small>(Demo only: <a href='$reset_link'>Click here to reset password</a>)</small>";
    } else {
        $error = $result['message'];
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="auth-container" style="max-width: 400px; margin: 40px auto;">
        <h1 class="page-title">Forgot Password</h1>
        
        <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <p class="info-text">Enter your email address and we'll send you a link to reset your password.</p>
        
        <form action="forgot-password.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-large btn-block">Send Reset Link</button>
            
            <div class="auth-links">
                <p><a href="login.php">Back to Login</a></p>
            </div>
        </form>
    </div>
</div>

<style>
.info-text {
    text-align: center;
    margin-bottom: 20px;
    color: #666;
}
</style>

<?php include 'includes/footer.php';