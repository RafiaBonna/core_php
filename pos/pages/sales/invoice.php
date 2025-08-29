<?php
// Include database connection file
include_once __DIR__ . '/../../config.php';

// Check if a sales ID is provided
if (!isset($_GET['sale_id'])) {
    die("Invalid sales ID provided.");
}

$sales_id = $_GET['sale_id'];

// Database connection instance (assuming $conn is globally available from config.php)
if (!isset($conn)) {
    die("Database connection not available.");
}

// Fetch the main sales record
$sql_sales = "SELECT s.*, c.name AS customer_name, c.phone FROM sales s LEFT JOIN customers c ON s.customer_id = c.id WHERE s.id = ?";
$stmt_sales = $conn->prepare($sql_sales);

// --- ERROR CHECK ---
if (!$stmt_sales) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

$stmt_sales->bind_param("i", $sales_id);
$stmt_sales->execute();
$result_sales = $stmt_sales->get_result();
$sale = $result_sales->fetch_assoc();

if (!$sale) {
    die("No sales record found for this ID.");
}

// Fetch the items for the sale
$sql_items = "SELECT si.*, st.product_name, st.sale_price FROM sale_items si JOIN stock st ON si.stock_id = st.id WHERE si.sale_id = ?";
$stmt_items = $conn->prepare($sql_items);

// --- ERROR CHECK ---
if (!$stmt_items) {
    die("Prepare failed for items: (" . $conn->errno . ") " . $conn->error);
}

$stmt_items->bind_param("i", $sales_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - Sale #<?php echo htmlspecialchars($sale['id']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 800px;
            margin: auto;
        }
        .invoice-header, .invoice-footer {
            text-align: center;
        }
        .invoice-header h1 {
            color: #333;
        }
        .invoice-details, .customer-details {
            margin-bottom: 20px;
        }
        .invoice-details table, .customer-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details th, .invoice-details td, .customer-details th, .customer-details td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .item-table th, .item-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .item-table th {
            background-color: #f2f2f2;
        }
        .total-row td {
            text-align: right;
            font-weight: bold;
        }
        .total-row td:first-child {
            border-right: none;
        }
        .text-right {
            text-align: right;
        }
        .no-border {
            border: none;
        }
    </style>
</head>
<body>

<div class="invoice-header">
    <h1>Invoice</h1>
    <p>Date: <?php echo htmlspecialchars($sale['sale_date']); ?></p>
</div>

<div class="customer-details">
    <h3>Customer Details</h3>
    <table>
        <tr>
            <th>Name:</th>
            <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
        </tr>
        <tr>
            <th>Phone:</th>
            <td><?php echo htmlspecialchars($sale['phone']); ?></td>
        </tr>
    </table>
</div>

<div class="invoice-details">
    <h3>Invoice Details</h3>
    <table class="item-table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            while ($item = $result_items->fetch_assoc()): 
                $grand_total += $item['total_price'];
            ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                <td><?php echo htmlspecialchars(number_format($item['unit_price'], 2)); ?></td>
                <td><?php echo htmlspecialchars(number_format($item['total_price'], 2)); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="no-border text-right"><strong>Grand Total:</strong></td>
                <td><strong><?php echo htmlspecialchars(number_format($grand_total, 2)); ?></strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="invoice-footer">
    <p>Thank you for your business!</p>
</div>

</body>
</html>