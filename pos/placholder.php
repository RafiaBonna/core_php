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
        include __DIR__ . '/pages/product/product.php'; // Combined Add/Manage Product Page
        break;
    case "8":
        include __DIR__ . '/pages/vendor/manage_vendor.php'; // Vendor Page
        break;
    case "9":
        include __DIR__ . '/pages/product/edit_product.php'; // New case for editing a product
        break;
    case "reports_sales":
        include __DIR__ . '/pages/reports/sales_report.php';
        break;
    case "reports_purchase":
        include __DIR__ . '/pages/reports/purchase_report.php';
        break;
    case "reports_inventory":
        include __DIR__ . '/pages/reports/inventory_report.php';
        break;
    case "reports_profit":
        include __DIR__ . '/pages/reports/profit_report.php';
        break;
    case "reports_customers":
        include __DIR__ . '/pages/reports/customers_report.php';
        break;
    case "reports_vendors":
        include __DIR__ . '/pages/reports/vendors_report.php';
        break;
    case "dashboard":
        include __DIR__ . '/dashboard.php';
        break;
    default:
        echo "<h4 class='text-center mt-5'>Page not found!</h4>";
        break;
}
?>