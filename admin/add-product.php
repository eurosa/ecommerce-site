<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate required fields
    if (empty($_POST['name']) || empty($_POST['description']) || empty($_POST['price']) || empty($_POST['stock'])) {
        $error = 'All fields are required';
    } else {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        
        // First insert product without image
        $query = "INSERT INTO products (name, description, price, stock) 
                  VALUES ('$name', '$description', $price, $stock)";
        
        if (mysqli_query($conn, $query)) {
            $product_id = mysqli_insert_id($conn);
            
            // Handle multiple image upload
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $upload_result = uploadMultipleImages($_FILES['images'], $product_id);
                
                if ($upload_result['success'] && !empty($upload_result['images'])) {
                    // Get the primary image (first image or selected primary)
                    $primary_image = isset($_POST['primary_image']) ? $_POST['primary_image'] : $upload_result['images'][0];
                    
                    // Save images to database
                    if (saveProductImages($conn, $product_id, $upload_result['images'], $primary_image)) {
                        $message = 'Product added successfully with ' . count($upload_result['images']) . ' images!';
                    } else {
                        $error = 'Product saved but failed to save some images to database.';
                    }
                } else {
                    $error = 'Failed to upload images: ' . implode(', ', $upload_result['errors']);
                    // Delete the product if images failed
                    mysqli_query($conn, "DELETE FROM products WHERE id = $product_id");
                }
            } else {
                $error = 'Please select at least one image';
                // Delete the product if no images
                mysqli_query($conn, "DELETE FROM products WHERE id = $product_id");
            }
        } else {
            $error = 'Database error: ' . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Multiple image upload styles */
        .image-upload-container {
            border: 2px dashed #ddd;
            padding: 20px;
            border-radius: 10px;
            background: #f9f9f9;
            margin-bottom: 20px;
        }
        
        .image-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .image-preview-item {
            position: relative;
            border: 2px solid #ddd;
            border-radius: 5px;
            padding: 5px;
            background: white;
        }
        
        .image-preview-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 3px;
        }
        
        .image-preview-item .primary-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: #ff6b6b;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .image-preview-item .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: none;
            font-size: 0.8rem;
        }
        
        .image-preview-item .remove-image:hover {
            background: #dc3545;
        }
        
        .image-preview-item .primary-selector {
            position: absolute;
            bottom: 5px;
            left: 5px;
            background: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 0.7rem;
            border: 1px solid #ddd;
            cursor: pointer;
        }
        
        .add-more-images {
            margin-top: 10px;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .upload-progress {
            margin-top: 10px;
            display: none;
        }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .progress-bar-fill {
            height: 100%;
            background: #ff6b6b;
            width: 0%;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="add-product.php" class="active"><i class="fas fa-plus-circle"></i> Add Product</a>
                <a href="view-orders.php"><i class="fas fa-shopping-cart"></i> View Orders</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
        
        <div class="admin-main">
            <h1 style="margin-bottom: 30px;">Add New Product</h1>
            
            <?php if ($message): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form action="add-product.php" method="POST" enctype="multipart/form-data" class="product-form" id="productForm">
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-tag"></i> Product Name *
                    </label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                           placeholder="Enter product name">
                </div>
                
                <div class="form-group">
                    <label for="description">
                        <i class="fas fa-align-left"></i> Description *
                    </label>
                    <textarea id="description" name="description" rows="5" required 
                              placeholder="Enter product description"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">
                            <i class="fas fa-dollar-sign"></i> Price ($) *
                        </label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required 
                               value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                               placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">
                            <i class="fas fa-boxes"></i> Stock Quantity *
                        </label>
                        <input type="number" id="stock" name="stock" min="0" required 
                               value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : ''; ?>"
                               placeholder="0">
                    </div>
                </div>
                
                <!-- Multiple Image Upload Section -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-images"></i> Product Images *
                    </label>
                    
                    <div class="image-upload-container">
                        <div style="text-align: center; padding: 20px;">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #ff6b6b;"></i>
                            <h3>Drag & Drop Images Here</h3>
                            <p>or</p>
                            <div class="file-input-wrapper">
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('imageInput').click()">
                                    <i class="fas fa-plus"></i> Select Images
                                </button>
                                <input type="file" id="imageInput" name="images[]" multiple accept="image/*" onchange="handleImageSelect(this)" required>
                            </div>
                            <p style="color: #666; margin-top: 10px;">
                                <small>Max 5MB per image. Allowed: JPG, PNG, GIF, WEBP</small>
                            </p>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="upload-progress" id="uploadProgress">
                            <div class="progress-bar">
                                <div class="progress-bar-fill" id="progressBarFill"></div>
                            </div>
                            <p id="progressText">Uploading...</p>
                        </div>
                        
                        <!-- Image Preview Grid -->
                        <div class="image-preview-grid" id="imagePreviewGrid"></div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Add Product
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
            
            <!-- Hidden input for primary image -->
            <input type="hidden" name="primary_image" id="primaryImageInput" value="">
        </div>
    </div>
    
    <script>
    let selectedFiles = [];
    let primaryImage = null;
    
    function handleImageSelect(input) {
        if (input.files) {
            // Add new files to selectedFiles array
            for (let i = 0; i < input.files.length; i++) {
                selectedFiles.push(input.files[i]);
            }
            
            // Show progress
            document.getElementById('uploadProgress').style.display = 'block';
            simulateUpload();
            
            // Preview images
            previewImages();
        }
    }
    
    function previewImages() {
        const previewGrid = document.getElementById('imagePreviewGrid');
        previewGrid.innerHTML = '';
        
        for (let i = 0; i < selectedFiles.length; i++) {
            const file = selectedFiles[i];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'image-preview-item';
                previewItem.dataset.index = i;
                
                // Create image
                const img = document.createElement('img');
                img.src = e.target.result;
                
                // Primary badge (shown if this is primary)
                const primaryBadge = document.createElement('span');
                primaryBadge.className = 'primary-badge';
                primaryBadge.textContent = 'PRIMARY';
                primaryBadge.style.display = (primaryImage === i) ? 'block' : 'none';
                
                // Remove button
                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-image';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.onclick = function() {
                    removeImage(i);
                };
                
                // Primary selector
                const primarySelector = document.createElement('div');
                primarySelector.className = 'primary-selector';
                primarySelector.innerHTML = '<input type="radio" name="primary_radio" ' + (primaryImage === i ? 'checked' : '') + ' onchange="setPrimaryImage(' + i + ')"> <small>Primary</small>';
                
                // File info
                const fileInfo = document.createElement('small');
                fileInfo.style.display = 'block';
                fileInfo.style.padding = '2px 5px';
                fileInfo.style.fontSize = '0.7rem';
                fileInfo.style.color = '#666';
                fileInfo.textContent = (file.size / 1024).toFixed(1) + ' KB';
                
                previewItem.appendChild(img);
                previewItem.appendChild(primaryBadge);
                previewItem.appendChild(removeBtn);
                previewItem.appendChild(primarySelector);
                previewItem.appendChild(fileInfo);
                
                previewGrid.appendChild(previewItem);
            }
            
            reader.readAsDataURL(file);
        }
        
        // Update the file input with all selected files
        updateFileInput();
    }
    
    function removeImage(index) {
        selectedFiles.splice(index, 1);
        
        if (primaryImage === index) {
            primaryImage = selectedFiles.length > 0 ? 0 : null;
        } else if (primaryImage > index) {
            primaryImage--;
        }
        
        previewImages();
    }
    
    function setPrimaryImage(index) {
        primaryImage = index;
        document.getElementById('primaryImageInput').value = selectedFiles[index].name;
        previewImages(); // Refresh to show primary badge
    }
    
    function updateFileInput() {
        // Create a new FileList from selectedFiles
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });
        
        document.getElementById('imageInput').files = dataTransfer.files;
    }
    
    function simulateUpload() {
        let progress = 0;
        const interval = setInterval(() => {
            progress += 10;
            document.getElementById('progressBarFill').style.width = progress + '%';
            
            if (progress >= 100) {
                clearInterval(interval);
                setTimeout(() => {
                    document.getElementById('uploadProgress').style.display = 'none';
                }, 500);
            }
        }, 100);
    }
    
    // Form validation
    document.getElementById('productForm').addEventListener('submit', function(e) {
        if (selectedFiles.length === 0) {
            e.preventDefault();
            alert('Please select at least one image.');
            return false;
        }
        
        // Check file sizes
        for (let i = 0; i < selectedFiles.length; i++) {
            if (selectedFiles[i].size > 5 * 1024 * 1024) {
                e.preventDefault();
                alert('File ' + selectedFiles[i].name + ' exceeds 5MB limit.');
                return false;
            }
        }
        
        // Set primary image
        if (primaryImage !== null) {
            document.getElementById('primaryImageInput').value = selectedFiles[primaryImage].name;
        }
    });
    
    // Drag and drop functionality
    const dropZone = document.querySelector('.image-upload-container');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        dropZone.style.background = '#e9ecef';
        dropZone.style.borderColor = '#ff6b6b';
    }
    
    function unhighlight() {
        dropZone.style.background = '#f9f9f9';
        dropZone.style.borderColor = '#ddd';
    }
    
    dropZone.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        document.getElementById('imageInput').files = files;
        handleImageSelect(document.getElementById('imageInput'));
    }
    </script>
</body>
</html>