<?php
// Start session if it's not already started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the 'page' parameter from the URL, defaulting to 'dashboard' if not set.
$page = $_GET['page'] ?? 'dashboard';

// Use a switch statement to include the correct page file.
switch ($page) {
    case "1":
        include __DIR__ . '/pages/user/add_user.php';
        break;
    case "2":
        include __DIR__ . '/pages/user/manage_user.php';
        break;
    case "3":
        include __DIR__ . '/pages/user/edit_user.php';
        break;
    case "4":
        include __DIR__ . '/pages/category/add_category.php';
        break;
    case "5":
        include __DIR__ . '/pages/category/manage_category.php';
        break;
    case "6":
        include __DIR__ . '/pages/category/edit_category.php';
        break;
    case "7":
        // This page will handle both adding and managing products.
        include __DIR__ . '/pages/product/product.php'; 
        break;
    case "8":
        // Vendor Page
        include __DIR__ . '/pages/vendor/manage_vendor.php';
        break;
    case "9":
        // Edit Product Page
        include __DIR__ . '/pages/product/edit_product.php';
        break;
    case "dashboard":
        include __DIR__ . '/dashboard.php';
        break;
    default:
        echo "<h4 class='text-center mt-5'>Page not found!</h4>";
        break;
}
?>