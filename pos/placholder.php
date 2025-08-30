<?php
// Start session if it's not already started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the 'page' parameter from the URL, defaulting to 'dashboard' if not set.
$page = $_GET['page'] ?? 'dashboard';

// Use a switch statement to include the correct page file.
switch ($page) {
    case "dashboard":
        // Dashboard Page
        include __DIR__ . '/pages/dashboard.php';
        break;
    case "1":
        // Add User Page
        include __DIR__ . '/pages/user/add_user.php';
        break;
    case "2":
        // Manage User Page
        include __DIR__ . '/pages/user/manage_user.php';
        break;
    case "3":
        // Edit User Page
        include __DIR__ . '/pages/user/edit_user.php';
        break;
    case "4":
        // Add Category Page
        include __DIR__ . '/pages/category/add_category.php';
        break;
    case "5":
        // Manage Category Page
        include __DIR__ . '/pages/category/manage_category.php';
        break;
    case "6":
        // Edit Category Page
        include __DIR__ . '/pages/category/edit_category.php';
        break;
    case "7":
        // Manage Product Page
        include __DIR__ . '/pages/product/product.php';
        break;
    case "8":
        // Manage Vendor Page
        include __DIR__ . '/pages/vendor/manage_vendor.php';
        break;
    case "9":
        // Edit Product Page
        include __DIR__ . '/pages/product/edit_product.php';
        break;
    case "10":
        // Manage Customer Page
        include __DIR__ . '/pages/customer/manage_customer.php';
        break;
    case "11":
        // Create Sale Page
        include __DIR__ . '/pages/sales/create_sale.php';
        break;
    case "12":
        // Sales History Page
        include __DIR__ . '/pages/sales/sales_history.php';
        break;
    case "13":
        // Sales Invoice Page
        include __DIR__ . '/pages/sales/invoice.php';
        break;
    case "14":
        // Sales Return Page
        include __DIR__ . '/pages/sales/sales_return.php';
        break;
    case "15":
        // Inventory Report Page
        include __DIR__ . '/pages/reports/reports_inventory.php';
        break;
    case "16":
        // Profit/Loss Report Page
        include __DIR__ . '/pages/reports/reports_profit.php';
        break;
    case "17":
        // Customer Report Page
        include __DIR__ . '/pages/reports/reports_customers.php';
        break;
    case "18":
        // Vendor Report Page
        include __DIR__ . '/pages/reports/reports_vendors.php';
        break;
    case "19":
        // Expired Products Page
        include __DIR__ .  '/pages/customer/edit_customer.php';
        break;
    case "20":
        // Expired Product Details Page
        include __DIR__ . '/pages/expired_products/expired_product_details.php';
        break;
    case "21":
        // Process Expired Product Page
        include __DIR__ . '/pages/expired_products/process_expired_product.php';
        break;
    default:
        // If page is not found, redirect to dashboard
        include __DIR__ . '/pages/dashboard.php';
        break;
}
?>
