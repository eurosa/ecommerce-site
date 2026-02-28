<?php
// Get all products
function getProducts($conn) {
    $query = "SELECT * FROM products ORDER BY created_at DESC";
    return mysqli_query($conn, $query);
}

// Get single product
function getProduct($conn, $id) {
    $query = "SELECT * FROM products WHERE id = $id";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Add to cart
function addToCart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

// Get cart items
function getCartItems($conn) {
    $items = array();
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $product = getProduct($conn, $product_id);
            if ($product) {
                $product['quantity'] = $quantity;
                $product['subtotal'] = $product['price'] * $quantity;
                $items[] = $product;
            }
        }
    }
    return $items;
}

// Calculate cart total
function getCartTotal($conn) {
    $total = 0;
    $items = getCartItems($conn);
    foreach ($items as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

// Place order
// Update placeOrder function in includes/functions.php
function placeOrder($conn, $customer_data, $cart_items, $total, $user_id = null) {
    if ($user_id) {
        $query = "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, customer_address, total_amount) 
                  VALUES ($user_id, '{$customer_data['name']}', '{$customer_data['email']}', 
                  '{$customer_data['phone']}', '{$customer_data['address']}', $total)";
    } else {
        $query = "INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, total_amount) 
                  VALUES ('{$customer_data['name']}', '{$customer_data['email']}', 
                  '{$customer_data['phone']}', '{$customer_data['address']}', $total)";
    }
    
    if (mysqli_query($conn, $query)) {
        $order_id = mysqli_insert_id($conn);
        
        // Insert order items
        foreach ($cart_items as $item) {
            $item_query = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price) 
                          VALUES ($order_id, {$item['id']}, '{$item['name']}', {$item['quantity']}, {$item['price']})";
            mysqli_query($conn, $item_query);
            
            // Update product stock
            $update_stock = "UPDATE products SET stock = stock - {$item['quantity']} WHERE id = {$item['id']}";
            mysqli_query($conn, $update_stock);
        }
        
        // Clear cart
        unset($_SESSION['cart']);
        
        return $order_id;
    } 
    return false;
}

// Currency format
function formatMoney($amount) {
    return '$' . number_format($amount, 2);
}



// Upload image
// Upload image with improved error handling
/**
 * Upload image with comprehensive error handling and debugging
 */
/**
 * Upload image with comprehensive error handling
 */
function uploadImage($file) {
    // Determine the correct upload path
    $upload_dir = __DIR__ . '/../uploads/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            error_log("Failed to create upload directory: $upload_dir");
            return false;
        }
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        error_log("Upload directory not writable: $upload_dir");
        // Try to set permissions
        chmod($upload_dir, 0777);
        if (!is_writable($upload_dir)) {
            return false;
        }
    }
    
    // Validate file
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        error_log("Invalid file type: " . $file['type']);
        return false;
    }
    
    if ($file['size'] > $max_size) {
        error_log("File too large: " . $file['size']);
        return false;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $target_path = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        error_log("File uploaded successfully: $filename");
        return $filename;
    } else {
        error_log("Failed to move uploaded file. Error: " . print_r(error_get_last(), true));
        return false;
    }
}

/**
 * Upload multiple images for a product
 */
function uploadMultipleImages($files, $product_id) {
    $uploaded_images = [];
    $errors = [];
    
    // Count how many files were uploaded
    $file_count = count($files['name']);
    
    for ($i = 0; $i < $file_count; $i++) {
        if ($files['error'][$i] == 0) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            $result = uploadImage($file);
            
            if ($result && isset($result['success']) && $result['success']) {
                $uploaded_images[] = $result['filename'];
            } else if (is_string($result) && !empty($result)) {
                $uploaded_images[] = $result;
            } else {
                $errors[] = "Failed to upload: " . $files['name'][$i];
            }
        }
    }
    
    return ['success' => empty($errors), 'images' => $uploaded_images, 'errors' => $errors];
}

/**
 * Save product images to database
 */
function saveProductImages($conn, $product_id, $images, $primary_image = null) {
    $success = true;
    
    foreach ($images as $index => $image) {
        $is_primary = ($image == $primary_image) ? 1 : 0;
        $sort_order = $index;
        
        $query = "INSERT INTO product_images (product_id, image_path, is_primary, sort_order) 
                  VALUES ($product_id, '$image', $is_primary, $sort_order)";
        
        if (!mysqli_query($conn, $query)) {
            $success = false;
            error_log("Failed to save product image: " . mysqli_error($conn));
        }
    }
    
    return $success;
}

/**
 * Get all images for a product
 */
function getProductImages($conn, $product_id) {
    $query = "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY is_primary DESC, sort_order ASC";
    $result = mysqli_query($conn, $query);
    
    $images = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $images[] = $row;
    }
    
    return $images;
}

/**
 * Get primary image for a product
 */
function getProductPrimaryImage($conn, $product_id) {
    $query = "SELECT image_path FROM product_images WHERE product_id = $product_id AND is_primary = 1 LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['image_path'];
    }
    
    // If no primary image, get the first image
    $query = "SELECT image_path FROM product_images WHERE product_id = $product_id ORDER BY sort_order ASC LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['image_path'];
    }
    
    return null;
}

/**
 * Delete product images
 */
function deleteProductImages($conn, $product_id) {
    // Get all images to delete files
    $query = "SELECT image_path FROM product_images WHERE product_id = $product_id";
    $result = mysqli_query($conn, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $file_path = __DIR__ . '/../uploads/' . $row['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Delete from database
    $query = "DELETE FROM product_images WHERE product_id = $product_id";
    return mysqli_query($conn, $query);
}
 