<?php
// Include the database connection file.
include_once __DIR__ . '/../../config.php';

$vendors = [];
$products = [];
$message = ''; // Message to the user.

// Fetch all vendors from the database.
$vendor_sql = "SELECT id, name FROM vendors ORDER BY name ASC";
$vendor_result = $conn->query($vendor_sql);
if ($vendor_result) {
    while ($row = $vendor_result->fetch_assoc()) {
        $vendors[] = $row;
    }
}

// Fetch all products from the database.
$product_sql = "SELECT id, product_name FROM products ORDER BY product_name ASC";
$product_result = $conn->query($product_sql);
if ($product_result) {
    while ($row = $product_result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Handle purchase form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_purchase'])) {
    // Start a transaction.
    $conn->begin_transaction();

    try {
        $vendor_id = intval($_POST['vendor_id']);
        $total_amount = floatval($_POST['total_amount']);
        $purchase_date = date('Y-m-d H:i:s');
        $product_ids = $_POST['product_id'];
        $quantities = $_POST['quantity'];
        $unit_prices = $_POST['unit_price'];
        $sale_prices = $_POST['sale_price'];
        $manufacture_dates = $_POST['manufacture_date']; 
        $expiry_dates = $_POST['expiry_date'];
        
        // --- 1. Insert into `purchases` table using a prepared statement.
        $sql_purchase = "INSERT INTO purchases (vendor_id, total_amount, purchase_date) VALUES (?, ?, ?)";
        $stmt_purchase = $conn->prepare($sql_purchase);
        $stmt_purchase->bind_param("ids", $vendor_id, $total_amount, $purchase_date);
        
        if (!$stmt_purchase->execute()) {
            throw new Exception("Error inserting into purchases: " . $stmt_purchase->error);
        }
        
        $purchase_id = $stmt_purchase->insert_id;
        $stmt_purchase->close();

        // --- 2. Loop through products and insert into `purchase_items` and update `stock`.
        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = intval($product_ids[$i]);
            $quantity = intval($quantities[$i]);
            $unit_price = floatval($unit_prices[$i]);
            $sale_price = floatval($sale_prices[$i]);
            $manufacture_date = !empty($manufacture_dates[$i]) ? $manufacture_dates[$i] : null; 
            $expiry_date = !empty($expiry_dates[$i]) ? $expiry_dates[$i] : null;

            // Check if the product already exists in stock
            $sql_check = "SELECT id FROM stock WHERE product_id = ?";
            $stmt_check = $conn->prepare($sql_check);
            if (!$stmt_check) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt_check->bind_param("i", $product_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            $stock_id = null;

            if ($result_check->num_rows > 0) {
                // If product exists, update the quantity
                $row = $result_check->fetch_assoc();
                $stock_id = $row['id'];
                $sql_stock_update = "UPDATE stock SET quantity = quantity + ?, purchase_price = ?, sale_price = ?, manufacture_date = ?, expiry_date = ?, vendor_id = ?, updated_at = ? WHERE product_id = ?";
                $stmt_stock_update = $conn->prepare($sql_stock_update);
                if (!$stmt_stock_update) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                $updated_at = date('Y-m-d H:i:s');
                $stmt_stock_update->bind_param("iddssisi", $quantity, $unit_price, $sale_price, $manufacture_date, $expiry_date, $vendor_id, $updated_at, $product_id);
                if (!$stmt_stock_update->execute()) {
                    throw new Exception("Error updating stock: " . $stmt_stock_update->error);
                }
                $stmt_stock_update->close();
            } else {
                // If product does not exist, insert a new record
                $sql_stock_insert = "INSERT INTO stock (product_id, quantity, purchase_price, sale_price, manufacture_date, expiry_date, vendor_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_stock_insert = $conn->prepare($sql_stock_insert);
                if (!$stmt_stock_insert) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                $created_at = date('Y-m-d H:i:s');
                $stmt_stock_insert->bind_param("iiddssis", $product_id, $quantity, $unit_price, $sale_price, $manufacture_date, $expiry_date, $vendor_id, $created_at);
                if (!$stmt_stock_insert->execute()) {
                    throw new Exception("Error inserting into stock: " . $stmt_stock_insert->error);
                }
                $stock_id = $stmt_stock_insert->insert_id;
                $stmt_stock_insert->close();
            }
            $stmt_check->close();

            // Insert into `purchase_items`
            $sql_item = "INSERT INTO purchase_items (purchase_id, stock_id, quantity, unit_price) VALUES (?, ?, ?, ?)";
            $stmt_item = $conn->prepare($sql_item);
            if (!$stmt_item) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt_item->bind_param("iiid", $purchase_id, $stock_id, $quantity, $unit_price);
            if (!$stmt_item->execute()) {
                throw new Exception("Error inserting into purchase_items: " . $stmt_item->error);
            }
            $stmt_item->close();
        }

        // If everything is successful, commit the transaction.
        $conn->commit();
        $message = "Purchase processed successfully!";
        // header("Location: ../../home.php?page=25&status=success&message=" . urlencode($message));
        // exit;

    } catch (Exception $e) {
        // If an error occurs, rollback the transaction.
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
        header("Location: ../../home.php?page=25&status=error&message=" . urlencode($message));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Purchase</title>
</head>
<body>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Create New Purchase</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">New Purchase</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Purchase Details</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($message)): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="vendor_id" class="form-label">Select Vendor</label>
                    <select name="vendor_id" id="vendor_id" class="form-select">
                        <option value="">Select a Vendor</option>
                        <?php foreach ($vendors as $vendor): ?>
                            <option value="<?= htmlspecialchars($vendor['id']); ?>"><?= htmlspecialchars($vendor['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="productList">
                    <div class="product-row row g-3">
                        <div class="col-md-12 mb-3">
                            <label for="product_id" class="form-label">Product Name</label>
                            <select name="product_id[]" class="form-select product-select" required>
                                <option value="">Select a Product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= htmlspecialchars($product['id']); ?>"><?= htmlspecialchars($product['product_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" name="quantity[]" class="form-control quantity-input" min="1" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="unit_price" class="form-label">Unit Price</label>
                            <input type="number" step="0.01" name="unit_price[]" class="form-control unit-price-input" min="0" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="sale_price" class="form-label">Sale Price</label>
                            <input type="number" step="0.01" name="sale_price[]" class="form-control sale-price-input" min="0" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="manufacture_date" class="form-label">Manufacture Date</label>
                            <input type="date" name="manufacture_date[]" class="form-control manufacture-date-input">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="expiry_date" class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date[]" class="form-control expiry-date-input">
                        </div>
                        <div class="col-md-1 d-flex align-items-end mb-3">
                            <button type="button" class="btn btn-danger remove-product-row">Remove</button>
                        </div>
                    </div>
                </div>

                <button type="button" id="addProductRowBtn" class="btn btn-primary mb-3">Add Another Product</button>

                <div class="mb-3">
                    <label for="total_amount" class="form-label">Total Amount</label>
                    <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control" readonly>
                </div>

                <button type="submit" name="process_purchase" class="btn btn-success w-100">Process Purchase</button>
            </form>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productList = document.getElementById('productList');
        const addProductRowBtn = document.getElementById('addProductRowBtn');
        const totalAmountInput = document.getElementById('total_amount');

        function createProductRow() {
            const productRow = document.createElement('div');
            productRow.className = 'product-row row g-3';
            productRow.innerHTML = `
                <div class="col-md-12 mb-3">
                    <label for="product_id" class="form-label">Product Name</label>
                    <select name="product_id[]" class="form-select product-select" required>
                        <option value="">Select a Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= htmlspecialchars($product['id']); ?>"><?= htmlspecialchars($product['product_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" name="quantity[]" class="form-control quantity-input" min="1" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="unit_price" class="form-label">Unit Price</label>
                    <input type="number" step="0.01" name="unit_price[]" class="form-control unit-price-input" min="0" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="sale_price" class="form-label">Sale Price</label>
                    <input type="number" step="0.01" name="sale_price[]" class="form-control sale-price-input" min="0" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="manufacture_date" class="form-label">Manufacture Date</label>
                    <input type="date" name="manufacture_date[]" class="form-control manufacture-date-input">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="expiry_date" class="form-label">Expiry Date</label>
                    <input type="date" name="expiry_date[]" class="form-control expiry-date-input">
                </div>
                <div class="col-md-1 d-flex align-items-end mb-3">
                    <button type="button" class="btn btn-danger remove-product-row">Remove</button>
                </div>
            `;
            return productRow;
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.product-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
                total += quantity * unitPrice;
            });
            totalAmountInput.value = total.toFixed(2);
        }

        productList.addEventListener('input', function(e) {
            if (e.target.classList.contains('quantity-input') || e.target.classList.contains('unit-price-input')) {
                calculateTotal();
            }
        });

        addProductRowBtn.addEventListener('click', function() {
            const newRow = createProductRow();
            productList.appendChild(newRow);
        });

        productList.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-product-row')) {
                e.target.closest('.product-row').remove();
                calculateTotal();
            }
        });

        calculateTotal(); // Initial calculation on page load.
    });
</script>
</body>
</html>