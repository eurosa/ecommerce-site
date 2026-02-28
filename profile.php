<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Require login
requireLogin();

$user = getCurrentUser($conn);
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $data = [
        'full_name' => mysqli_real_escape_string($conn, $_POST['full_name']),
        'phone' => mysqli_real_escape_string($conn, $_POST['phone']),
        'address' => mysqli_real_escape_string($conn, $_POST['address']),
        'city' => mysqli_real_escape_string($conn, $_POST['city']),
        'state' => mysqli_real_escape_string($conn, $_POST['state']),
        'zip_code' => mysqli_real_escape_string($conn, $_POST['zip_code']),
        'country' => mysqli_real_escape_string($conn, $_POST['country'])
    ];
    
    $result = updateUserProfile($conn, $user['id'], $data);
    
    if ($result['success']) {
        $success = $result['message'];
        $user = getCurrentUser($conn); // Refresh user data
    } else {
        $error = $result['message'];
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters';
    } elseif ($new_password != $confirm_password) {
        $error = 'New passwords do not match';
    } else {
        $result = changePassword($conn, $user['id'], $old_password, $new_password);
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

$countries = ['USA', 'Canada', 'UK', 'Australia', 'Other'];
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <h1 class="page-title">My Profile</h1>
    
    <?php if ($error): ?>
    <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="profile-tabs">
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('profile')">Profile Information</button>
            <button class="tab-btn" onclick="showTab('orders')">Order History</button>
            <button class="tab-btn" onclick="showTab('password')">Change Password</button>
        </div>
        
        <!-- Profile Information Tab -->
        <div id="profile-tab" class="tab-content active">
            <form action="profile.php" method="POST" class="profile-form">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="form-section">
                    <h2>Account Information</h2>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled class="readonly-field">
                        <small>Username cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled class="readonly-field">
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Personal Information</h2>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" 
                                   value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="state">State/Province</label>
                            <input type="text" id="state" name="state" 
                                   value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="zip_code">ZIP/Postal Code</label>
                            <input type="text" id="zip_code" name="zip_code" 
                                   value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="country">Country</label>
                            <select id="country" name="country">
                                <option value="">Select Country</option>
                                <?php foreach ($countries as $country): ?>
                                <option value="<?php echo $country; ?>" <?php echo ($user['country'] ?? '') == $country ? 'selected' : ''; ?>>
                                    <?php echo $country; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
        
        <!-- Orders Tab -->
        <div id="orders-tab" class="tab-content">
            <?php
            $orders = getUserOrders($conn, $user['id']);
            if (mysqli_num_rows($orders) > 0):
            ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($orders)): ?>
                    <tr>
                        <td>#<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="order-status status-<?php echo $order['order_status']; ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary btn-small">View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-orders">
                <p>You haven't placed any orders yet.</p>
                <a href="products.php" class="btn btn-primary">Start Shopping</a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Change Password Tab -->
        <div id="password-tab" class="tab-content">
            <form action="profile.php" method="POST" class="password-form">
                <input type="hidden" name="change_password" value="1">
                
                <div class="form-group">
                    <label for="old_password">Current Password *</label>
                    <input type="password" id="old_password" name="old_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                    <small>Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>
</div>

<style>
.profile-tabs {
    margin: 40px 0;
}

.tab-buttons {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #ddd;
    padding-bottom: 10px;
}

.tab-btn {
    padding: 10px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    color: #666;
    border-radius: 5px 5px 0 0;
}

.tab-btn.active {
    background: #ff6b6b;
    color: white;
}

.tab-content {
    display: none;
    padding: 20px;
    background: white;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.tab-content.active {
    display: block;
}

.readonly-field {
    background: #f5f5f5;
    cursor: not-allowed;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
}

.orders-table td {
    padding: 15px;
    border-bottom: 1px solid #ddd;
}

.order-status {
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 0.9rem;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.btn-small {
    padding: 5px 10px;
    font-size: 0.9rem;
}

.empty-orders {
    text-align: center;
    padding: 40px;
}

.empty-orders p {
    margin-bottom: 20px;
    color: #666;
}
</style>

<script>
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}
</script>

<?php include 'includes/footer.php';