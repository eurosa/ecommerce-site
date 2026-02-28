// Add to cart confirmation
document.addEventListener('DOMContentLoaded', function() {
    // Handle add to cart buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const productName = this.dataset.productName;
            showNotification(`${productName} added to cart!`);
        });
    });
    
    // Quantity input validation
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const min = parseInt(this.min) || 0;
            const max = parseInt(this.max) || 999;
            let value = parseInt(this.value);
            
            if (isNaN(value) || value < min) {
                this.value = min;
            } else if (value > max) {
                this.value = max;
                showNotification(`Maximum quantity is ${max}`, 'warning');
            }
        });
    });
    
    // Image preview for admin panel
    const imageInput = document.querySelector('input[type="file"][name="image"]');
    
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.style.maxWidth = '200px';
                    preview.style.marginTop = '10px';
                    
                    const existingPreview = document.querySelector('.image-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                    
                    preview.classList.add('image-preview');
                    imageInput.parentNode.appendChild(preview);
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = this.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    
                    // Add error message
                    let errorMsg = field.parentNode.querySelector('.error-message');
                    if (!errorMsg) {
                        errorMsg = document.createElement('small');
                        errorMsg.classList.add('error-message');
                        errorMsg.textContent = 'This field is required';
                        field.parentNode.appendChild(errorMsg);
                    }
                } else {
                    field.classList.remove('error');
                    const errorMsg = field.parentNode.querySelector('.error-message');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'error');
            }
        });
    });
});

// Notification system
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Style the notification
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.backgroundColor = type === 'success' ? '#4CAF50' : '#f44336';
    notification.style.color = 'white';
    notification.style.padding = '15px 25px';
    notification.style.borderRadius = '5px';
    notification.style.boxShadow = '0 3px 10px rgba(0,0,0,0.2)';
    notification.style.zIndex = '9999';
    notification.style.display = 'flex';
    notification.style.alignItems = 'center';
    notification.style.gap = '10px';
    notification.style.animation = 'slideIn 0.3s ease';
    
    document.body.appendChild(notification);
    
    // Add animation keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            notification.remove();
            style.remove();
        }, 300);
    }, 3000);
}

// Add to cart animation
function animateAddToCart(button) {
    const productCard = button.closest('.product-card');
    const productImage = productCard.querySelector('img');
    const cartIcon = document.querySelector('.nav-cart i');
    
    if (productImage && cartIcon) {
        const imageClone = productImage.cloneNode(true);
        imageClone.style.position = 'fixed';
        imageClone.style.width = '50px';
        imageClone.style.height = '50px';
        imageClone.style.borderRadius = '50%';
        imageClone.style.zIndex = '9999';
        imageClone.style.transition = 'all 1s ease';
        
        const rect = productImage.getBoundingClientRect();
        const cartRect = cartIcon.getBoundingClientRect();
        
        imageClone.style.left = rect.left + 'px';
        imageClone.style.top = rect.top + 'px';
        
        document.body.appendChild(imageClone);
        
        setTimeout(() => {
            imageClone.style.left = cartRect.left + 'px';
            imageClone.style.top = cartRect.top + 'px';
            imageClone.style.width = '20px';
            imageClone.style.height = '20px';
            imageClone.style.opacity = '0';
        }, 10);
        
        setTimeout(() => {
            imageClone.remove();
            // Bounce cart icon
            cartIcon.style.transform = 'scale(1.3)';
            setTimeout(() => {
                cartIcon.style.transform = 'scale(1)';
            }, 200);
        }, 1010);
    }
}

// Search functionality
function searchProducts(query) {
    const products = document.querySelectorAll('.product-card');
    let hasResults = false;
    
    products.forEach(product => {
        const title = product.querySelector('h3').textContent.toLowerCase();
        const description = product.querySelector('.product-description')?.textContent.toLowerCase() || '';
        
        if (title.includes(query.toLowerCase()) || description.includes(query.toLowerCase())) {
            product.style.display = 'block';
            hasResults = true;
        } else {
            product.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    let noResultsMsg = document.querySelector('.no-results');
    
    if (!hasResults) {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results';
            noResultsMsg.innerHTML = '<h3>No products found</h3><p>Try different keywords</p>';
            noResultsMsg.style.textAlign = 'center';
            noResultsMsg.style.padding = '40px';
            document.querySelector('.product-grid').appendChild(noResultsMsg);
        }
    } else if (noResultsMsg) {
        noResultsMsg.remove();
    }
}

// Price range filter
function filterByPrice(min, max) {
    const products = document.querySelectorAll('.product-card');
    
    products.forEach(product => {
        const priceText = product.querySelector('.product-price').textContent;
        const price = parseFloat(priceText.replace('$', ''));
        
        if (price >= min && price <= max) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

// Sort products
function sortProducts(sortBy) {
    const productGrid = document.querySelector('.product-grid');
    const products = Array.from(document.querySelectorAll('.product-card'));
    
    products.sort((a, b) => {
        const aVal = a.querySelector(sortBy === 'price' ? '.product-price' : 'h3').textContent;
        const bVal = b.querySelector(sortBy === 'price' ? '.product-price' : 'h3').textContent;
        
        if (sortBy === 'price') {
            return parseFloat(aVal.replace('$', '')) - parseFloat(bVal.replace('$', ''));
        } else {
            return aVal.localeCompare(bVal);
        }
    });
    
    productGrid.innerHTML = '';
    products.forEach(product => productGrid.appendChild(product));
}

// Mobile menu toggle
function toggleMobileMenu() {
    const navMenu = document.querySelector('.nav-menu');
    const mobileBtn = document.querySelector('.mobile-menu-btn i');
    
    navMenu.classList.toggle('active');
    
    if (navMenu.classList.contains('active')) {
        mobileBtn.classList.remove('fa-bars');
        mobileBtn.classList.add('fa-times');
    } else {
        mobileBtn.classList.remove('fa-times');
        mobileBtn.classList.add('fa-bars');
    }
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const navMenu = document.querySelector('.nav-menu');
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    
    if (!navMenu.contains(event.target) && !mobileBtn.contains(event.target)) {
        navMenu.classList.remove('active');
        document.querySelector('.mobile-menu-btn i').classList.remove('fa-times');
        document.querySelector('.mobile-menu-btn i').classList.add('fa-bars');
    }
});

// Cart animation when item added
function animateCart() {
    const cartIcon = document.querySelector('.nav-cart i');
    cartIcon.classList.add('cart-bounce');
    setTimeout(() => {
        cartIcon.classList.remove('cart-bounce');
    }, 300);
}

// Update cart count dynamically
function updateCartCount(count) {
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        cartCount.textContent = count;
        animateCart();
    }
}

// User dropdown for mobile
document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown on mobile
    if (window.innerWidth <= 768) {
        const userNav = document.querySelector('.nav-user');
        if (userNav) {
            userNav.addEventListener('click', function(e) {
                e.preventDefault();
                const dropdown = document.querySelector('.dropdown-content');
                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            });
        }
    }
});

// Prevent dropdown from closing immediately on hover out
let dropdownTimeout;
const userDropdown = document.querySelector('.nav-user-dropdown');

if (userDropdown) {
    userDropdown.addEventListener('mouseleave', function() {
        dropdownTimeout = setTimeout(() => {
            this.querySelector('.dropdown-content').style.display = 'none';
        }, 300);
    });
    
    userDropdown.addEventListener('mouseenter', function() {
        clearTimeout(dropdownTimeout);
        this.querySelector('.dropdown-content').style.display = 'block';
    });
}