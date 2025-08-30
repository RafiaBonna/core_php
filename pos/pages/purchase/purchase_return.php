<?php
// Include the database connection file.
include_once __DIR__ . '/../../config.php';

$message = '';

// Fetch all purchases to populate the dropdown
$purchases = [];
$sql_purchases = "SELECT id, purchase_date, vendor_id FROM purchases ORDER BY purchase_date DESC";
$result_purchases = $conn->query($sql_purchases);
if ($result_purchases && $result_purchases->num_rows > 0) {
    while ($row = $result_purchases->fetch_assoc()) {
        $purchases[] = $row;
    }
}

// Handle form submission for a purchase return
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_return'])) {
    $conn->begin_transaction();

    try {
        $purchase_id = intval($_POST['purchase_id']);
        $product_id = intval($_POST['product_id']);
        $quantity_returned = intval($_POST['quantity_returned']);
        $reason = trim($_POST['reason']);
        
        if ($purchase_id <= 0 || $product_id <= 0 || $quantity_returned <= 0) {
            throw new Exception("Invalid input. Please check the values.");
        }

        // --- 1. Check if the returned quantity is valid
        $sql_check_quantity = "SELECT quantity FROM stock WHERE product_id = ?";
        $stmt_check = $conn->prepare($sql_check_quantity);
        $stmt_check->bind_param("i", $product_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows == 0) {
            throw new Exception("Product not found in stock.");
        }
        
        $stock_row = $result_check->fetch_assoc();
        $available_quantity = $stock_row['quantity'];
        $stmt_check->close();

        if ($quantity_returned > $available_quantity) {
            throw new Exception("Cannot return more products than available in stock. Available: " . $available_quantity);
        }

        // --- 2. Update the `stock` table by reducing the quantity
        $sql_update_stock = "UPDATE stock SET quantity = quantity - ?, updated_at = ? WHERE product_id = ?";
        $stmt_update_stock = $conn->prepare($sql_update_stock);
        $updated_at = date('Y-m-d H:i:s');
        $stmt_update_stock->bind_param("isi", $quantity_returned, $updated_at, $product_id);
        if (!$stmt_update_stock->execute()) {
            throw new Exception("Error updating stock quantity: " . $stmt_update_stock->error);
        }
        $stmt_update_stock->close();

        // --- 3. Insert the return record into the `purchase_returns` table
        $sql_insert_return = "INSERT INTO purchase_returns (purchase_id, stock_id, returned_quantity, reason) VALUES (?, ?, ?, ?)";
        $stmt_insert_return = $conn->prepare($sql_insert_return);
        $stmt_insert_return->bind_param("iiis", $purchase_id, $product_id, $quantity_returned, $reason);
        if (!$stmt_insert_return->execute()) {
            throw new Exception("Error recording purchase return: " . $stmt_insert_return->error);
        }
        $stmt_insert_return->close();

        $conn->commit();
        $message = "<div class='alert alert-success'>Purchase return processed successfully!</div>";

    } catch (Exception $e) {
        $conn->rollback();
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// Fetch all products from stock for the dropdown
$products = [];
$sql_products = "SELECT p.id, p.product_name, s.quantity FROM products p LEFT JOIN stock s ON p.id = s.product_id ORDER BY p.product_name ASC";
$result_products = $conn->query($sql_products);
if ($result_products && $result_products->num_rows > 0) {
    while ($row = $result_products->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Purchase Return</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Purchase Return</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Process Purchase Return</h3>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="purchase_id" class="form-label">Select Purchase Invoice</label>
                    <select name="purchase_id" id="purchase_id" class="form-select" required>
                        <option value="">Select a Purchase</option>
                        <?php foreach ($purchases as $purchase): ?>
                            <option value="<?= htmlspecialchars($purchase['id']); ?>">Invoice #<?= htmlspecialchars($purchase['id']); ?> (<?= htmlspecialchars($purchase['purchase_date']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="product_id" class="form-label">Product</label>
                    <select name="product_id" id="product_id" class="form-select" required>
                        <option value="">Select a Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= htmlspecialchars($product['id']); ?>">
                                <?= htmlspecialchars($product['product_name']); ?> (Stock: <?= htmlspecialchars($product['quantity']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="quantity_returned" class="form-label">Quantity to Return</label>
                    <input type="number" name="quantity_returned" id="quantity_returned" class="form-control" min="1" required>
                </div>
                <div class="mb-3">
                    <label for="reason" class="form-label">Reason for Return</label>
                    <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Enter reason for return..."></textarea>
                </div>
                <button type="submit" name="process_return" class="btn btn-danger w-100">Process Return</button>
            </form>
        </div>
    </div>
</div>