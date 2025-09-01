<?php
// Include the database connection file.
include_once __DIR__ . '/../../config.php';

// Check if a purchase ID is provided in the URL.
if (!isset($_GET['purchase_id']) || !is_numeric($_GET['purchase_id'])) {
    die("Error: No valid purchase ID provided.");
}

$purchase_id = intval($_GET['purchase_id']);

// Fetch purchase and vendor details
$sql_purchase = "SELECT p.*, 
                        v.name AS vendor_name, 
                        v.contact_person AS vendor_contact, 
                        v.phone AS vendor_phone, 
                        v.email AS vendor_email, 
                        v.address AS vendor_address
                 FROM purchases p
                 LEFT JOIN vendors v ON p.vendor_id = v.id
                 WHERE p.id = ?";
$stmt_purchase = $conn->prepare($sql_purchase);
$stmt_purchase->bind_param("i", $purchase_id);
$stmt_purchase->execute();
$result_purchase = $stmt_purchase->get_result();
$purchase = $result_purchase->fetch_assoc();
$stmt_purchase->close();

if (!$purchase) {
    die("Error: No purchase record found for this ID.");
}

// Fetch purchase items
$sql_items = "SELECT pi.*, pr.product_name 
              FROM purchase_items pi
              JOIN stock st ON pi.stock_id = st.id
              JOIN products pr ON st.product_id = pr.id
              WHERE pi.purchase_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $purchase_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$stmt_items->close();
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Purchase Invoice</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="home.php?page=dashboard">Home</a></li>
                    <li class="breadcrumb-item"><a href="home.php?page=17">Purchase History</a></li>
                    <li class="breadcrumb-item active">Invoice #<?php echo htmlspecialchars($purchase['id']); ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Invoice Details</h3>
            <div class="card-tools">
                <a href="#" class="btn btn-info btn-sm" onclick="window.print()">Print Invoice</a>
            </div>
        </div>
        <div class="card-body">
            <div class="invoice p-3 mb-3">
                <div class="row">
                    <div class="col-12">
                        <h4>
                            <i class="fas fa-file-invoice"></i> Purchase Invoice
                            <small class="float-right">Date: <?php echo date('Y-m-d', strtotime($purchase['purchase_date'])); ?></small>
                        </h4>
                    </div>
                </div>

                <div class="row invoice-info">
                    <!-- Vendor (From) -->
                    <div class="col-sm-4 invoice-col">
                        From
                        <address>
                            <strong><?php echo htmlspecialchars($purchase['vendor_name']); ?></strong><br>
                            <?php echo nl2br(htmlspecialchars($purchase['vendor_address'] ?? '')); ?><br>
                            Contact: <?php echo htmlspecialchars($purchase['vendor_contact'] ?? ''); ?><br>
                            Phone: <?php echo htmlspecialchars($purchase['vendor_phone'] ?? 'N/A'); ?><br>
                            Email: <?php echo htmlspecialchars($purchase['vendor_email'] ?? 'N/A'); ?>
                        </address>
                    </div>

                    <!-- Company (To) -->
                    <div class="col-sm-4 invoice-col">
                        To
                        <address>
                            <strong>DREAM POS</strong><br>
                            123 Business Street, Dhaka, Bangladesh<br>
                            Phone: +880 1234 567 890<br>
                            Email: info@dreampos.com
                        </address>
                    </div>

                    <!-- Invoice Info -->
                    <div class="col-sm-4 invoice-col">
                        <b>Invoice #<?php echo htmlspecialchars($purchase['id']); ?></b><br><br>
                        <b>Purchase Date:</b> <?php echo date('d/m/Y', strtotime($purchase['purchase_date'])); ?><br>
                        <b>Total Amount:</b> $<?php echo number_format($purchase['total_amount'], 2); ?>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12 table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Unit Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $item_counter = 1;
                                if ($result_items->num_rows > 0):
                                    while ($item = $result_items->fetch_assoc()):
                                        $subtotal = $item['unit_price'] * $item['quantity'];
                                ?>
                                <tr>
                                    <td><?php echo $item_counter++; ?></td>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td>$<?php echo number_format($subtotal, 2); ?></td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No items found for this invoice.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 text-right">
                        <p class="lead"><b>Total: $<?php echo number_format($purchase['total_amount'], 2); ?></b></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
