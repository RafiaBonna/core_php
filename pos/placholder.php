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
        include __DIR__ . '/pages/dashboard.php';
        break;
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
        // Edit Product
        include __DIR__ . '/pages/product/edit_product.php';
        break;
    case "10":
        // Customer Page
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
        include __DIR__ . '/pages/reports/reports_inventory.php';
        break;
    case "16":
        include __DIR__ . '/pages/reports/reports_profit.php';
        break;
    case "17":
        include __DIR__ . '/pages/reports/reports_customers.php';
        break;
    case "18":
        include __DIR__ . '/pages/reports/reports_vendors.php';
        break;
    case "19":
        include __DIR__ . '/pages/customer/edit_customer.php';
        break;
    case "20":
        // Add Stock Page
        include __DIR__ . '/pages/stock/add_stock.php';
        break;
    default:
        // Default to dashboard if page is not recognized
        include __DIR__ . '/pages/dashboard.php';
        break;
}
?>