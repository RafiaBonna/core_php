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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 16px;
            line-height: 24px;
            background-color: #fff;
        }
        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }
        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }
        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }
        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }
        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }
        .invoice-box table tr.item.last td {
            border-bottom: none;
        }
        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
        .invoice-footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
        }
        .print-button {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            text-align: center;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="invoice-box">
    <table cellpadding="0" cellspacing="0">
        <tr class="top">
            <td colspan="4">
                <table>
                    <tr>
                        <td class="title">
                            <h1>POS System</h1>
                        </td>
                        <td style="text-align: right;">
                            Invoice #: **<?php echo htmlspecialchars($sale['id']); ?>**<br>
                            Created: <?php echo date("F d, Y", strtotime(htmlspecialchars($sale['sale_date']))); ?><br>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="information">
            <td colspan="4">
                <table>
                    <tr>
                        <td>
                            <p style="font-weight: bold;">M&R Company</p>
                            123 Main Street<br>
                             Dhanmondi,Dhaka-Bangladesh 1204<br>
                            M&R@company.com
                        </td>
                        <td style="text-align: right;">
                            <p style="font-weight: bold;">Billed to</p>
                            <?php echo htmlspecialchars($sale['customer_name'] ?: 'Guest'); ?><br>
                            <?php echo htmlspecialchars($sale['phone'] ?: 'N/A'); ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="heading">
            <td>Item</td>
            <td style="text-align: center;">Price</td>
            <td style="text-align: center;">Quantity</td>
            <td style="text-align: right;">Total</td>
        </tr>

        <?php 
        $grand_total = 0;
        $item_count = $result_items->num_rows;
        $current_item = 0;

        while ($item = $result_items->fetch_assoc()): 
            $grand_total += $item['total_price'];
            $current_item++;
            $is_last = ($current_item == $item_count);
        ?>
        <tr class="item <?php echo $is_last ? 'last' : ''; ?>">
            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
            <td style="text-align: center;"><?php echo htmlspecialchars(number_format($item['unit_price'], 2)); ?></td>
            <td style="text-align: center;"><?php echo htmlspecialchars($item['quantity']); ?></td>
            <td style="text-align: right;"><?php echo htmlspecialchars(number_format($item['total_price'], 2)); ?></td>
        </tr>
        <?php endwhile; ?>

        <tr class="total">
            <td colspan="3"></td>
            <td style="text-align: right;">
                Total: **<?php echo htmlspecialchars(number_format($grand_total, 2)); ?>**
            </td>
        </tr>
    </table>
    
    <div class="invoice-footer">
        <p>Thank you for your business!</p>
        <p>For any inquiries, please contact us at M&R @company.com</p>
    </div>
</div>

<button onclick="window.print()" class="print-button">Print Invoice</button>

</body>
</html>