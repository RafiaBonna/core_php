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
        // Manage Products
        include __DIR__ . '/pages/product/product.php';
        break;
    case "8":
        // Manage Vendors
        include __DIR__ . '/pages/vendor/manage_vendor.php';
        break;
    case "9":
        // Edit Product
        include __DIR__ . '/pages/product/edit_product.php';
        break;
    case "10":
        // Edit Vendor
        include __DIR__ . '/pages/vendor/edit_vendor.php';
        break;
    case "11":
        // POS Sales
        include __DIR__ . '/pages/sales/pos_sales.php';
        break;
    case "12":
        // Sales History
        include __DIR__ . '/pages/sales/sales_history.php';
        break;
    case "13":
        // Customer
        include __DIR__ . '/pages/customer/manage_customer.php';
        break;
    case "14":
        // Add Customer
        include __DIR__ . '/pages/customer/add_customer.php';
        break;
    case "15":
        // Edit Customer
        include __DIR__ . '/pages/customer/edit_customer.php';
        break;
    case "16":
        // Expense Category
        include __DIR__ . '/pages/expense/expense_category.php';
        break;
    case "17":
        // Add Expense
        include __DIR__ . '/pages/expense/add_expense.php';
        break;
    case "18":
        // Manage Expense
        include __DIR__ . '/pages/expense/manage_expense.php';
        break;
    case "19":
        // Edit Expense
        include __DIR__ . '/pages/expense/edit_expense.php';
        break;
    case "20":
        // Manage Sales Return
        include __DIR__ . '/pages/sales_return/sales_return.php';
        break;
    case "21":
        // Stock Report
        include __DIR__ . '/pages/stock/stock.php';
        break;
    case "22":
        // Expired Products
        include __DIR__ . '/pages/expired_products/expired_products.php';
        break;
    case "23":
        // Create Purchase
        include __DIR__ . '/pages/purchase/create_purchase.php';
        break;
    case "24":
        // Purchase History
        include __DIR__ . '/pages/purchase/purchase_history.php';
        break;
    case "25":
        // Purchase Invoice
        include __DIR__ . '/pages/purchase/purchase_invoice.php';
        break;
    case "26":
        // Add Product
        include __DIR__ . '/pages/product/add_product.php';
        break;
    case "27":
        // Manage Vendors (Duplicate, kept for reference)
        include __DIR__ . '/pages/vendor/manage_vendor.php';
        break;
    case "28":
        // Purchase Return
        include __DIR__ . '/pages/purchase/purchase_return.php';
        break;
    case "reports_sales":
        // Sales Report
        include __DIR__ . '/pages/reports/reports_sales.php';
        break;
    case "reports_inventory":
        // Inventory Report
        include __DIR__ . '/pages/reports/reports_inventory.php';
        break;
    case "reports_profit":
        // Profit/Loss Report
        include __DIR__ . '/pages/reports/reports_profit.php';
        break;
    case "reports_customers":
        // Customers Report
        include __DIR__ . '/pages/reports/reports_customers.php';
        break;
    case "reports_vendors":
        // Vendors Report
        include __DIR__ . '/pages/reports/reports_vendors.php';
        break;
    default:
        // Default to a 404 page or dashboard if the page is not found.
        include __DIR__ . '/pages/dashboard.php';
        break;
}
?>