<?php
// Include database connection
include_once __DIR__ . '/../../config.php';

// Check for database connection errors.
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products from stock with available quantity
$medicinesData = [];
$medicines = $conn->query("
    SELECT
        s.id AS stock_id,
        p.product_name,
        s.sale_price,
        s.quantity
    FROM stock s
    JOIN products p ON s.product_id = p.id
    WHERE s.quantity > 0
    ORDER BY p.product_name ASC
");
if ($medicines) {
    while ($row = $medicines->fetch_assoc()) {
        $medicinesData[] = $row;
    }
}

// Fetch customers
$customersData = [];
$customers = $conn->query("SELECT id, name FROM customers ORDER BY name ASC");
if ($customers) {
    while ($row = $customers->fetch_assoc()) {
        $customersData[] = $row;
    }
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grand_total'])) {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $total_amount = floatval($_POST['grand_total'] ?? 0);
    $stock_ids = $_POST['stock_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $unit_prices = $_POST['unit_price'] ?? [];
    $product_names = $_POST['product_name'] ?? [];

    // Begin a transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        // 1. Create a new customer if they don't exist
        $customer_id = null;
        if (!empty($customer_name)) {
            $check_customer = $conn->prepare("SELECT id FROM customers WHERE name = ?");
            $check_customer->bind_param("s", $customer_name);
            $check_customer->execute();
            $result = $check_customer->get_result();

            if ($result->num_rows > 0) {
                $customer_id = $result->fetch_assoc()['id'];
            } else {
                $insert_customer = $conn->prepare("INSERT INTO customers (name, created_at) VALUES (?, NOW())");
                $insert_customer->bind_param("s", $customer_name);
                $insert_customer->execute();
                $customer_id = $conn->insert_id;
            }
        }

        // 2. Insert the sale record
        $sale_date = date('Y-m-d H:i:s');
        $sale_insert_sql = "INSERT INTO sales (customer_id, total_amount, sale_date) VALUES (?, ?, ?)";
        $sale_stmt = $conn->prepare($sale_insert_sql);
        $sale_stmt->bind_param("ids", $customer_id, $total_amount, $sale_date);
        $sale_stmt->execute();
        $sale_id = $conn->insert_id;

        // 3. Process each sale item and update stock
        for ($i = 0; $i < count($stock_ids); $i++) {
            $stock_id = intval($stock_ids[$i]);
            $quantity = intval($quantities[$i]);
            $unit_price = floatval($unit_prices[$i]);
            $total_price = $quantity * $unit_price;

            // Insert into sale_items
            $sale_item_insert_sql = "INSERT INTO sale_items (sale_id, stock_id, quantity, unit_price, total_price) 
                                     VALUES (?, ?, ?, ?, ?)";
            $sale_item_stmt = $conn->prepare($sale_item_insert_sql);
            $sale_item_stmt->bind_param("iiidd", $sale_id, $stock_id, $quantity, $unit_price, $total_price);
            $sale_item_stmt->execute();

            // Update the stock quantity
            $stock_update_sql = "UPDATE stock SET quantity = quantity - ? WHERE id = ?";
            $stock_stmt = $conn->prepare($stock_update_sql);
            $stock_stmt->bind_param("ii", $quantity, $stock_id);
            $stock_stmt->execute();
        }

        // All operations successful, commit the transaction
        $conn->commit();
        $message = "✅ Sale processed successfully!";
    } catch (Exception $e) {
        // Something went wrong, rollback the transaction
        $conn->rollback();
        $message = "❌ Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Sale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>Create New Sale</h1>

        <?php if ($message) : ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="customer_name" class="form-label">Customer Name</label>
                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
            </div>

            <div id="product-list" class="mb-3">
                <div class="row sale-item-row">
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Product</label>
                        <select class="form-select product-select" name="stock_id[]" required>
                            <option value="">Select Product</option>
                            <?php foreach ($medicinesData as $medicine) : ?>
                                <option value="<?= htmlspecialchars($medicine['stock_id']) ?>"
                                        data-price="<?= htmlspecialchars($medicine['sale_price']) ?>"
                                        data-product-name="<?= htmlspecialchars($medicine['product_name']) ?>"
                                        data-quantity="<?= htmlspecialchars($medicine['quantity']) ?>">
                                    <?= htmlspecialchars($medicine['product_name']) ?> (Stock: <?= htmlspecialchars($medicine['quantity']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" class="product-name-input-hidden" name="product_name[]">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control quantity-input" name="quantity[]" min="1" value="1" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Unit Price</label>
                        <input type="number" step="0.01" class="form-control unit-price-input" name="unit_price[]" readonly required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Total</label>
                        <input type="number" step="0.01" class="form-control total-input" readonly>
                    </div>
                    <div class="col-md-1 d-flex align-items-end mb-3">
                        <button type="button" class="btn btn-danger remove-row" style="display: none;">Remove</button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-primary" id="add-product-row">Add Another Product</button>

            <div class="mt-4">
                <div class="row justify-content-end">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text">Grand Total</span>
                            <input type="number" step="0.01" class="form-control" id="grand-total-input" name="grand_total" readonly required>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success mt-3">Process Sale</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            const productList = $('#product-list');
            const grandTotalInput = $('#grand-total-input');

            function updateRowTotal(row) {
                const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
                const unitPrice = parseFloat(row.find('.unit-price-input').val()) || 0;
                const total = quantity * unitPrice;
                row.find('.total-input').val(total.toFixed(2));
                updateGrandTotal();
            }

            function updateGrandTotal() {
                let grandTotal = 0;
                $('.sale-item-row').each(function() {
                    const rowTotal = parseFloat($(this).find('.total-input').val()) || 0;
                    grandTotal += rowTotal;
                });
                grandTotalInput.val(grandTotal.toFixed(2));
            }

            productList.on('change', '.product-select', function() {
                const selectedOption = $(this).find('option:selected');
                const unitPrice = selectedOption.data('price');
                const productName = selectedOption.data('product-name');
                const row = $(this).closest('.sale-item-row');

                row.find('.unit-price-input').val(unitPrice);
                row.find('.product-name-input-hidden').val(productName);
                updateRowTotal(row);
            });

            productList.on('input', '.quantity-input', function() {
                const row = $(this).closest('.sale-item-row');
                updateRowTotal(row);
            });

            productList.on('click', '.remove-row', function() {
                if ($('.sale-item-row').length > 1) {
                    $(this).closest('.sale-item-row').remove();
                } else {
                    const row = $(this).closest('.sale-item-row');
                    row.find('.product-select').val('');
                    row.find('.quantity-input').val(1);
                    row.find('.unit-price-input').val('');
                    row.find('.total-input').val('');
                    row.find('.product-name-input-hidden').val('');
                }
                updateGrandTotal();
            });

            $('#add-product-row').on('click', function() {
                const newRow = $('.sale-item-row').first().clone();
                newRow.find('.product-select').val('');
                newRow.find('.quantity-input').val(1);
                newRow.find('.unit-price-input').val('');
                newRow.find('.total-input').val('');
                newRow.find('.product-name-input-hidden').val('');
                newRow.find('.remove-row').show();
                productList.append(newRow);
                updateGrandTotal();
            });

            updateGrandTotal();
        });
    </script>
</body>
</html>
