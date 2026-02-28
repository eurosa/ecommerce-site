<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_data = [
        'username' => mysqli_real_escape_string($conn, $_POST['username']),
        'email' => mysqli_real_escape_string($conn, $_POST['email']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password'],
        'full_name' => mysqli_real_escape_string($conn, $_POST['full_name']),
        'phone' => mysqli_real_escape_string($conn, $_POST['phone']),
        'address' => mysqli_real_escape_string($conn, $_POST['address']),
        'city' => mysqli_real_escape_string($conn, $_POST['city']),
        'state' => mysqli_real_escape_string($conn, $_POST['state']),
        'zip_code' => mysqli_real_escape_string($conn, $_POST['zip_code']),
        'country' => mysqli_real_escape_string($conn, $_POST['country'])
    ];
    
    // Validation
    if (strlen($user_data['password']) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($user_data['password'] != $user_data['confirm_password']) {
        $error = 'Passwords do not match';
    } elseif (!filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        $result = registerUser($conn, $user_data);
        
        if ($result['success']) {
            $success = 'Registration successful! You can now login.';
            // Auto login
            loginUser($conn, $user_data['username'], $user_data['password']);
            header('Location: profile.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$countries = ['USA', 'Canada', 'UK', 'Australia', 'Other'];
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="auth-container" style="max-width: 800px; margin: 40px auto;">
        <h1 class="page-title">Create Account</h1>
        
        <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="POST" class="auth-form">
            <div class="form-section">
                <h2>Account Information</h2>
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required minlength="6">
                        <small>Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Personal Information</h2>
                
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required 
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="2"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" 
                               value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="state">State/Province</label>
                        <input type="text" id="state" name="state" 
                               value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="zip_code">ZIP/Postal Code</label>
                        <input type="text" id="zip_code" name="zip_code" 
                               value="<?php echo isset($_POST['zip_code']) ? htmlspecialchars($_POST['zip_code']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="country">Country</label>
                        <select id="country" name="country">
                            <option value="">Select Country</option>
                            <?php foreach ($countries as $country): ?>
                            <option value="<?php echo $country; ?>" <?php echo (isset($_POST['country']) && $_POST['country'] == $country) ? 'selected' : ''; ?>>
                                <?php echo $country; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">Register</button>
            </div>
            
            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </form>
    </div>
</div>

<style>
.form-section {
    background: #f9f9f9;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.form-section h2 {
    margin-bottom: 20px;
    font-size: 1.2rem;
    color: #333;
}

.auth-form small {
    color: #666;
    font-size: 0.8rem;
}

.auth-links {
    text-align: center;
    margin-top: 20px;
}

.auth-links a {
    color: #ff6b6b;
    text-decoration: none;
}

.auth-links a:hover {
    text-decoration: underline;
}
</style>

<?php include 'includes/footer.php'; 