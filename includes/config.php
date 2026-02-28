<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce_db');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set timezone
date_default_timezone_set('America/New_York');

// Site configuration
define('SITE_NAME', 'My E-Commerce Store');
define('SITE_URL', 'http://localhost/ecommerce-site/');
define('ADMIN_EMAIL', 'admin@yourstore.com');

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get company info
$company_query = "SELECT * FROM company_info LIMIT 1";
$company_result = mysqli_query($conn, $company_query);
$company = mysqli_fetch_assoc($company_result);
