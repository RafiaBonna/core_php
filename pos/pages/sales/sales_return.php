<?php
include_once __DIR__ . '/../../config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_return'])) {
    $sale_id = intval($_POST['sale_id']);
    $product_id = intval($_POST['product_id']);
    $quantity_returned = intval($_POST['quantity_returned']);

    if ($sale_id <= 0 || $product_id <= 0 || $quantity_returned <= 0) {
        $message = "<div class='alert alert-danger'>Invalid input values.</div>";
    } else {
        $conn->begin_transaction();
        try {
            // 1. Check if the product exists in the sale.
            $check_sql = "SELECT quantity FROM sale_items WHERE sale_id = ? AND stock_id = ?";
            $stmt_check = $conn->prepare($check_sql);
            if (!$stmt_check) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_check->bind_param("ii", $sale_id, $product_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows === 0) throw new Exception("Product not found in this sale.");
            $sale_item = $result->fetch_assoc();
            $sold_quantity = $sale_item['quantity'];

            // 2. Check previous returns.
            $returned_sql = "SELECT SUM(quantity_returned) AS total_returned FROM sales_return WHERE sale_id = ? AND product_id = ?";
            $stmt_returned = $conn->prepare($returned_sql);
            if (!$stmt_returned) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_returned->bind_param("ii", $sale_id, $product_id);
            $stmt_returned->execute();
            $returned_result = $stmt_returned->get_result()->fetch_assoc();
            $total_returned = $returned_result['total_returned'] ?? 0;

            if (($total_returned + $quantity_returned) > $sold_quantity) {
                throw new Exception("Return quantity exceeds the original sold quantity.");
            }

            // 3. Insert return record.
            $stmt_insert = $conn->prepare("INSERT INTO sales_return (sale_id, product_id, quantity_returned, return_date) VALUES (?, ?, ?, NOW())");
            if (!$stmt_insert) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_insert->bind_param("iii", $sale_id, $product_id, $quantity_returned);
            if (!$stmt_insert->execute()) throw new Exception("Error inserting return: " . $stmt_insert->error);

            // 4. Update stock.
            $stmt_update = $conn->prepare("UPDATE stock SET quantity = quantity + ? WHERE id = ?");
            if (!$stmt_update) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_update->bind_param("ii", $quantity_returned, $product_id);
            if (!$stmt_update->execute()) throw new Exception("Error updating stock: " . $stmt_update->error);

            $conn->commit();
            $message = "<div class='alert alert-success'>Sale return processed successfully!</div>";

        } catch (Exception $e) {
            $conn->rollback();
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Fetch sales
$sales = [];
$sales_result = $conn->query("SELECT id FROM sales ORDER BY id DESC");
if ($sales_result && $sales_result->num_rows > 0) {
    while ($row = $sales_result->fetch_assoc()) $sales[] = $row;
}

// Fetch products
$products = [];
$product_result = $conn->query("SELECT id, product_name FROM stock ORDER BY product_name ASC");
if ($product_result && $product_result->num_rows > 0) {
    while ($row = $product_result->fetch_assoc()) $products[] = $row;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Sales Return</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Sales Return</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title">Process a Sales Return</h3>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            <form action="" method="post">
                <div class="mb-3">
                    <label for="sale_id" class="form-label">Sale ID</label>
                    <select name="sale_id" id="sale_id" class="form-select" required>
                        <option value="">Select a Sale ID</option>
                        <?php foreach ($sales as $sale): ?>
                            <option value="<?= htmlspecialchars($sale['id']); ?>"><?= htmlspecialchars($sale['id']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="product_id" class="form-label">Product</label>
                    <select name="product_id" id="product_id" class="form-select" required>
                        <option value="">Select a Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= htmlspecialchars($product['id']); ?>"><?= htmlspecialchars($product['product_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="quantity_returned" class="form-label">Quantity to Return</label>
                    <input type="number" name="quantity_returned" id="quantity_returned" class="form-control" min="1" required>
                </div>
                <button type="submit" name="process_return" class="btn btn-danger w-100">Process Return</button>
            </form>
        </div>
    </div>
</div>
