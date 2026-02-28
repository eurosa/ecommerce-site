<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['action'])) {
    $action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
    
    if ($action == 'add') {
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        addToCart($product_id, $quantity);
        header('Location: cart.php');
        exit;
    }
    
    if ($action == 'update' && isset($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id] = $quantity;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        }
        header('Location: cart.php');
        exit;
    }
    
    if ($action == 'remove' && isset($_GET['id'])) {
        unset($_SESSION['cart'][(int)$_GET['id']]);
        header('Location: cart.php');
        exit;
    }
    
    if ($action == 'clear') {
        unset($_SESSION['cart']);
        header('Location: cart.php');
        exit;
    }
}

$cart_items = getCartItems($conn);
$cart_total = getCartTotal($conn);
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <h1 class="page-title">Shopping Cart</h1>
    
    <?php if (empty($cart_items)): ?>
    <div class="empty-cart">
        <i class="fas fa-shopping-cart"></i>
        <h2>Your cart is empty</h2>
        <p>Looks like you haven't added any items yet</p>
        <a href="products.php" class="btn btn-primary">Continue Shopping</a>
    </div>
    <?php else: ?>
    
    <form action="cart.php" method="POST" class="cart-form">
        <input type="hidden" name="action" value="update">
        
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td>
                        <div class="cart-product">
                            <img src="uploads/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                            <div>
                                <h3><?php echo $item['name']; ?></h3>
                                <p><?php echo substr($item['description'], 0, 30); ?>...</p>
                            </div>
                        </div>
                    </td>
                    <td><?php echo formatMoney($item['price']); ?></td>
                    <td>
                        <input type="number" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="0" max="<?php echo $item['stock']; ?>" class="quantity-input">
                    </td>
                    <td><?php echo formatMoney($item['subtotal']); ?></td>
                    <td>
                        <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="remove-item"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="cart-actions">
            <button type="submit" class="btn btn-secondary">Update Cart</button>
            <a href="cart.php?action=clear" class="btn btn-danger" onclick="return confirm('Clear all items?')">Clear Cart</a>
        </div>
    </form>
    
    <div class="cart-summary">
        <h2>Cart Summary</h2>
        <div class="summary-row">
            <span>Subtotal:</span>
            <span><?php echo formatMoney($cart_total); ?></span>
        </div>
        <div class="summary-row">
            <span>Shipping:</span>
            <span>Free</span>
        </div>
        <div class="summary-row total">
            <span>Total:</span>
            <span><?php echo formatMoney($cart_total); ?></span>
        </div>
        
        <a href="checkout.php" class="btn btn-primary btn-large btn-block">Proceed to Checkout</a>
        <a href="products.php" class="btn btn-secondary btn-large btn-block">Continue Shopping</a>
    </div>
    
    <?php endif; ?>
</div>

<?php include 'includes/footer.php';