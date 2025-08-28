<?php
include_once __DIR__ . '/../../config.php';

// Check if a sale ID is provided in the URL
if (!isset($_GET['sale_id']) || !is_numeric($_GET['sale_id'])) {
    die("Error: Invalid or missing Sale ID.");
}

$sale_id = intval($_GET['sale_id']);
$sale_data = null;
$sale_items = [];

// Fetch the main sale details.
$sql_sale = "SELECT s.id, c.customer_name, s.total_amount, s.payment_method, s.sales_date 
             FROM sales s
             LEFT JOIN customers c ON s.customer_id = c.id
             WHERE s.id = ?";
$stmt_sale = $conn->prepare($sql_sale);
$stmt_sale->bind_param("i", $sale_id);
$stmt_sale->execute();
$result_sale = $stmt_sale->get_result();

if ($result_sale->num_rows > 0) {
    $sale_data = $result_sale->fetch_assoc();

    // Fetch the products associated with this sale
    $sql_items = "SELECT si.quantity, si.unit_price, p.product_name 
                  FROM sales_items si
                  JOIN stock p ON si.product_id = p.id
                  WHERE si.sale_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("i", $sale_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    
    while ($row = $result_items->fetch_assoc()) {
        $sale_items[] = $row;
    }

} else {
    die("Error: Sale not found.");
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Invoice</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item"><a href="home.php?page=11">Sales History</a></li>
                    <li class="breadcrumb-item active">Invoice</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="invoice p-3 mb-3">
        <div class="row">
            <div class="col-12">
                <h4>
                    <i class="fas fa-receipt"></i> DREAM POS
                    <small class="float-right">Date: <?php echo date('Y-m-d H:i:s', strtotime($sale_data['sales_date'])); ?></small>
                </h4>
            </div>
        </div>
        <div class="row invoice-info">
            <div class="col-sm-4 invoice-col">
                From
                <address>
                    <strong>DREAM POS</strong><br>
                    123, Main Street<br>
                    Dhaka, Bangladesh<br>
                    Phone: (880) 123-456789<br>
                    Email: info@dreampos.com
                </address>
            </div>
            <div class="col-sm-4 invoice-col">
                To
                <address>
                    <strong><?php echo htmlspecialchars($sale_data['customer_name'] ? $sale_data['customer_name'] : 'Guest Customer'); ?></strong><br>
                    <p>Address: N/A</p>
                    <p>Phone: N/A</p>
                    <p>Email: N/A</p>
                </address>
            </div>
            <div class="col-sm-4 invoice-col">
                <b>Invoice #<?php echo htmlspecialchars($sale_data['id']); ?></b><br>
                <br>
                <b>Sale ID:</b> <?php echo htmlspecialchars($sale_data['id']); ?><br>
                <b>Payment Method:</b> <?php echo htmlspecialchars($sale_data['payment_method']); ?><br>
            </div>
        </div>

        <div class="row">
            <div class="col-12 table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $item_count = 1; ?>
                        <?php foreach($sale_items as $item): ?>
                        <tr>
                            <td><?php echo $item_count++; ?></td>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td>$<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <p class="lead">Payment Method: <?php echo htmlspecialchars($sale_data['payment_method']); ?></p>
            </div>
            <div class="col-6">
                <p class="lead">Amount Due: $<?php echo number_format($sale_data['total_amount'], 2); ?></p>
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th style="width:50%">Subtotal:</th>
                            <td>$<?php echo number_format($sale_data['total_amount'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Tax (0%)</th>
                            <td>$0.00</td>
                        </tr>
                        <tr>
                            <th>Shipping:</th>
                            <td>$0.00</td>
                        </tr>
                        <tr>
                            <th>Total:</th>
                            <td>$<?php echo number_format($sale_data['total_amount'], 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="row no-print">
            <div class="col-12">
                <a href="#" onclick="window.print();" rel="noopener" target="_blank" class="btn btn-default"><i class="fas fa-print"></i> Print</a>
            </div>
        </div>
    </div>
</div>