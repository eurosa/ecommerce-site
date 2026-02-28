<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';  // ADD THIS LINE for isLoggedIn() function

$cart_items = getCartItems($conn);
$cart_total = getCartTotal($conn);

if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

$order_placed = false;
$order_id = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_data = [
        'name' => mysqli_real_escape_string($conn, $_POST['name']),
        'email' => mysqli_real_escape_string($conn, $_POST['email']),
        'phone' => mysqli_real_escape_string($conn, $_POST['phone']),
        'address' => mysqli_real_escape_string($conn, $_POST['address'])
    ];
    
    // Add user_id if logged in
    $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
    
    $order_id = placeOrder($conn, $customer_data, $cart_items, $cart_total, $user_id);
    
    if ($order_id) {
        $order_placed = true;
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <?php if ($order_placed): ?>
    <div class="order-confirmation">
        <i class="fas fa-check-circle"></i>
        <h1>Thank You for Your Order!</h1>
        <p>Your order has been placed successfully.</p>
        <p>Order ID: #<?php echo str_pad($order_id, 8, '0', STR_PAD_LEFT); ?></p>
        <p>We'll send a confirmation email to <?php echo htmlspecialchars($_POST['email']); ?></p>
        <a href="products.php" class="btn btn-primary">Continue Shopping</a>
    </div>
    
    <?php else: ?>
    
    <h1 class="page-title">Checkout</h1>
    
    <div class="checkout-container">
        <div class="checkout-form">
            <h2>Shipping Information</h2>
            
            <?php if (isLoggedIn()): 
                $user = getCurrentUser($conn);
            ?>
            <div class="logged-in-info">
                <p><i class="fas fa-user-check"></i> You're logged in as <strong><?php echo htmlspecialchars($user['email']); ?></strong></p>
                <p>Shipping information will be saved to your account.</p>
            </div>
            <?php endif; ?>
            
            <form action="checkout.php" method="POST">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo isLoggedIn() ? htmlspecialchars($user['full_name'] ?? '') : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isLoggedIn() ? htmlspecialchars($user['email'] ?? '') : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required 
                           value="<?php echo isLoggedIn() ? htmlspecialchars($user['phone'] ?? '') : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Shipping Address *</label>
                    <textarea id="address" name="address" rows="3" required><?php echo isLoggedIn() ? htmlspecialchars($user['address'] ?? '') : ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" 
                               value="<?php echo isLoggedIn() ? htmlspecialchars($user['city'] ?? '') : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" id="state" name="state" 
                               value="<?php echo isLoggedIn() ? htmlspecialchars($user['state'] ?? '') : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="zip_code">ZIP Code</label>
                        <input type="text" id="zip_code" name="zip_code" 
                               value="<?php echo isLoggedIn() ? htmlspecialchars($user['zip_code'] ?? '') : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" 
                               value="<?php echo isLoggedIn() ? htmlspecialchars($user['country'] ?? '') : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes">Order Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Special instructions for delivery..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-large btn-block">Place Order</button>
            </form>
            
            <div class="checkout-note">
                <p><i class="fas fa-lock"></i> Your information is secure and encrypted</p>
            </div>
        </div>
        
        <div class="checkout-summary">
            <h2>Order Summary</h2>
            <?php foreach ($cart_items as $item): ?>
            <div class="summary-item">
                <div class="summary-item-details">
                    <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                    <span class="item-quantity">x <?php echo $item['quantity']; ?></span>
                </div>
                <span class="item-price"><?php echo formatMoney($item['subtotal']); ?></span>
            </div>
            <?php endforeach; ?>
            
            <div class="summary-divider"></div>
            
            <div class="summary-row">
                <span>Subtotal:</span>
                <span><?php echo formatMoney($cart_total); ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping:</span>
                <span class="free-shipping">Free</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span><?php echo formatMoney($cart_total); ?></span>
            </div>
            
            <?php if (!isLoggedIn()): ?>
            <div class="checkout-guest-note">
                <p><i class="fas fa-info-circle"></i> 
                    <a href="login.php">Login</a> or <a href="register.php">create an account</a> to save your information for faster checkout next time.
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<style>
/* Additional checkout styles */
.logged-in-info {
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.logged-in-info p {
    margin: 5px 0;
    color: #0d47a1;
}

.logged-in-info i {
    color: #2196f3;
    margin-right: 8px;
}

.checkout-note {
    text-align: center;
    margin-top: 20px;
    color: #666;
    font-size: 0.9rem;
}

.checkout-note i {
    color: #28a745;
    margin-right: 5px;
}

.free-shipping {
    color: #28a745;
    font-weight: 500;
}

.checkout-guest-note {
    background: #fff3cd;
    border-radius: 5px;
    padding: 15px;
    margin-top: 20px;
    font-size: 0.9rem;
    color: #856404;
}

.checkout-guest-note i {
    margin-right: 5px;
}

.checkout-guest-note a {
    color: #ff6b6b;
    text-decoration: none;
    font-weight: 500;
}

.checkout-guest-note a:hover {
    text-decoration: underline;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding: 5px 0;
    border-bottom: 1px dashed #eee;
}

.summary-item-details {
    display: flex;
    flex-direction: column;
}

.item-name {
    font-weight: 500;
}

.item-quantity {
    font-size: 0.85rem;
    color: #666;
}

.item-price {
    font-weight: 500;
    color: #ff6b6b;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
}
</style>

<?php include 'includes/footer.php';