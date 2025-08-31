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
        include __DIR__ . '/pages/user/delete_user.php';
        break;
    case "5":
        include __DIR__ . '/pages/category/manage_category.php';
        break;
    case "6":
        include __DIR__ . '/pages/category/edit_category.php';
        break;
    case "7":
    
        include __DIR__ . '/pages/category/add_category.php';
        break;
    case "8":
       include __DIR__ . '/pages/category/delete_category.php';
        break;
    case "9":
        include __DIR__ . '/pages/product/product.php';
        break;
    case "10":
       include __DIR__ . '/pages/product/process_expired_product.php';
        break;
    case "11":
       include __DIR__ . '/pages/product/edit_product.php';
        break;
    case "12":
        // Sales History
        include __DIR__ . '/pages/sales/create_sale.php';
        break;
    case "13":
        // Customer
      include __DIR__ . '/pages/sales/sales_history.php';
        break;
    case "14":
        // Add Customer
        include __DIR__ . '/pages/sales/sales_return.php';
        break;
    case "15":
        // Edit Customer
        include __DIR__ . '/pages/sales/invoice.php';
        break;
    case "16":
        // Create Purchase
        include __DIR__ . '/pages/purchase/create_purchase.php';
        break;
    case "17":
         // Purchase History
        include __DIR__ . '/pages/purchase/purchase_history.php';
        break;
    case "18":
         // Purchase Invoice
        include __DIR__ . '/pages/purchase/purchase_invoice.php';
        break;
    case "19":
        // Purchase Invoice
        include __DIR__ . '/pages/purchase/purchase_return.php';
        break;
    case "20":
         // Manage Vendors
        include __DIR__ . '/pages/vendor/manage_vendor.php';
        break;
    case "21":
        // Stock Report
        include __DIR__ . '/pages/stock/stock.php';
        break;
    case "22":
        // Expired Products
        include __DIR__ . '/pages/customer/expired_products.php';
        break;
    case "23":
        // Create Purchase
        include __DIR__ . '/pages/customer/manage_customer.php';
        break;
    case "24":
        // Create Purchase
        include __DIR__ . '/pages/customer/edit_customer.php';
        break;
    case "25":
        // Purchase Invoice
        include __DIR__ . '/pages/expired_products/expired_products.php';
        break;
    case "26":
        // Add Product
        include __DIR__ . '/pages/expired_products/move_to_expired.php';
        break;
        case "27":
        // Sales Report
        include __DIR__ . '/pages/reports/purchase_report.php';
        break;
    case "28":
        // Sales Report
        include __DIR__ . '/pages/reports/sales_report.php';
        break;
    case "29":
        // Inventory Report
        include __DIR__ . '/pages/reports/inventory_report.php';
        break;
    case "30":
        // Profit/Loss Report
        include __DIR__ . '/pages/reports/profit&loss_report.php';
        break;
    case "31":
        // Customers Report
        include __DIR__ . '/pages/reports/customers_report.php';
        break;
    case "32":
        // Vendors Report
        include __DIR__ . '/pages/reports/vendors_report.php';
        break;
    default:
        // Default to a 404 page or dashboard if the page is not found.
        include __DIR__ . '/pages/dashboard.php';
        break;
}
?>