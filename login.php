<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: profile.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    $result = loginUser($conn, $username, $password);
    
    if ($result['success']) {
        // Set remember me cookie (30 days)
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + (30 * 24 * 60 * 60);
            setcookie('remember_token', $token, $expires, '/', '', true, true);
            // Store token in database (you'd need a remember_tokens table)
        }
        
        // Redirect to previous page or profile
        $redirect = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'profile.php';
        unset($_SESSION['redirect_url']);
        header("Location: $redirect");
        exit;
    } else {
        $error = $result['message'];
    }
}

// Check for success message from registration
if (isset($_GET['registered'])) {
    $success = 'Registration successful! Please login.';
}
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="auth-container" style="max-width: 400px; margin: 40px auto;">
        <h1 class="page-title">Login</h1>
        
        <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember"> Remember me
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-large btn-block">Login</button>
            
            <div class="auth-links">
                <p><a href="forgot-password.php">Forgot Password?</a></p>
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </form>
    </div>
</div>

<style>
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0;
}
</style>

<?php include 'includes/footer.php';