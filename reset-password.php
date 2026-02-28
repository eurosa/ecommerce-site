<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$error = '';
$success = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Validate token
$reset_data = validateResetToken($conn, $token);

if (!$reset_data && $_SERVER['REQUEST_METHOD'] != 'POST') {
    $error = 'Invalid or expired reset link.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password != $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $result = resetPassword($conn, $token, $password);
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="auth-container" style="max-width: 400px; margin: 40px auto;">
        <h1 class="page-title">Reset Password</h1>
        
        <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="success-message">
            <?php echo $success; ?>
            <p style="margin-top: 15px;"><a href="login.php" class="btn btn-primary">Login Now</a></p>
        </div>
        <?php elseif ($reset_data || isset($_POST['token'])): ?>
        
        <form action="reset-password.php" method="POST" class="auth-form">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="form-group">
                <label for="password">New Password *</label>
                <input type="password" id="password" name="password" required minlength="6">
                <small>Minimum 6 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-large btn-block">Reset Password</button>
        </form>
        
        <?php endif; ?>
        
        <div class="auth-links">
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php';