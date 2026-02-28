<?php
require_once 'config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Register a new user
 */
function registerUser($conn, $user_data) {
    // Check if username exists
    $check_username = mysqli_query($conn, "SELECT id FROM users WHERE username = '{$user_data['username']}'");
    if (mysqli_num_rows($check_username) > 0) {
        return ['success' => false, 'message' => 'Username already taken'];
    }
    
    // Check if email exists
    $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '{$user_data['email']}'");
    if (mysqli_num_rows($check_email) > 0) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Hash password
    $hashed_password = password_hash($user_data['password'], PASSWORD_DEFAULT);
    
    // Insert user
    $query = "INSERT INTO users (username, email, password, full_name, phone, address, city, state, zip_code, country) 
              VALUES (
                  '{$user_data['username']}',
                  '{$user_data['email']}',
                  '$hashed_password',
                  '{$user_data['full_name']}',
                  '{$user_data['phone']}',
                  '{$user_data['address']}',
                  '{$user_data['city']}',
                  '{$user_data['state']}',
                  '{$user_data['zip_code']}',
                  '{$user_data['country']}'
              )";
    
    if (mysqli_query($conn, $query)) {
        $user_id = mysqli_insert_id($conn);
        return ['success' => true, 'user_id' => $user_id];
    }
    
    return ['success' => false, 'message' => 'Registration failed: ' . mysqli_error($conn)];
}

/**
 * Login user
 */
function loginUser($conn, $username, $password) {
    $query = "SELECT * FROM users WHERE username = '$username' OR email = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['logged_in'] = true;
            
            return ['success' => true, 'user' => $user];
        }
    }
    
    return ['success' => false, 'message' => 'Invalid username or password'];
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Get current user data
 */
function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    
    return mysqli_fetch_assoc($result);
}

/**
 * Update user profile
 */
function updateUserProfile($conn, $user_id, $data) {
    $updates = [];
    
    $allowed_fields = ['full_name', 'phone', 'address', 'city', 'state', 'zip_code', 'country'];
    
    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = '{$data[$field]}'";
        }
    }
    
    if (empty($updates)) {
        return ['success' => false, 'message' => 'No data to update'];
    }
    
    $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = $user_id";
    
    if (mysqli_query($conn, $query)) {
        return ['success' => true, 'message' => 'Profile updated successfully'];
    }
    
    return ['success' => false, 'message' => 'Update failed: ' . mysqli_error($conn)];
}

/**
 * Change user password
 */
function changePassword($conn, $user_id, $old_password, $new_password) {
    // Get current password
    $query = "SELECT password FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
    
    if (!password_verify($old_password, $user['password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
    
    if (mysqli_query($conn, $update)) {
        return ['success' => true, 'message' => 'Password changed successfully'];
    }
    
    return ['success' => false, 'message' => 'Password change failed'];
}

/**
 * Get user orders
 */
function getUserOrders($conn, $user_id) {
    $query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
    return mysqli_query($conn, $query);
}

/**
 * Get single order details with items
 */
function getOrderDetails($conn, $order_id, $user_id) {
    $query = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id";
    $result = mysqli_query($conn, $query);
    $order = mysqli_fetch_assoc($result);
    
    if ($order) {
        $items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
        $order['items'] = mysqli_query($conn, $items_query);
    }
    
    return $order;
}

/**
 * Generate password reset token
 */
function generateResetToken($conn, $email) {
    // Check if user exists
    $query = "SELECT id FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 0) {
        return ['success' => false, 'message' => 'Email not found'];
    }
    
    $user = mysqli_fetch_assoc($result);
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Delete old tokens
    mysqli_query($conn, "DELETE FROM password_resets WHERE user_id = {$user['id']}");
    
    // Insert new token
    $insert = "INSERT INTO password_resets (user_id, token, expires_at) VALUES ({$user['id']}, '$token', '$expires')";
    
    if (mysqli_query($conn, $insert)) {
        return ['success' => true, 'token' => $token];
    }
    
    return ['success' => false, 'message' => 'Failed to generate reset token'];
}

/**
 * Validate reset token
 */
function validateResetToken($conn, $token) {
    $query = "SELECT pr.*, u.email FROM password_resets pr 
              JOIN users u ON pr.user_id = u.id 
              WHERE pr.token = '$token' AND pr.expires_at > NOW()";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

/**
 * Reset password with token
 */
function resetPassword($conn, $token, $new_password) {
    $reset = validateResetToken($conn, $token);
    
    if (!$reset) {
        return ['success' => false, 'message' => 'Invalid or expired token'];
    }
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update = "UPDATE users SET password = '$hashed_password' WHERE id = {$reset['user_id']}";
    
    if (mysqli_query($conn, $update)) {
        // Delete used token
        mysqli_query($conn, "DELETE FROM password_resets WHERE token = '$token'");
        return ['success' => true, 'message' => 'Password reset successfully'];
    }
    
    return ['success' => false, 'message' => 'Password reset failed'];
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit;
    }
}

/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}
