<?php
include_once __DIR__ . '/../../config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_return'])) {
    $sale_id = intval($_POST['sale_id']);
    $stock_id = intval($_POST['product_id']); // Renamed for clarity with schema
    $quantity_returned = intval($_POST['quantity_returned']);

    if ($sale_id <= 0 || $stock_id <= 0 || $quantity_returned <= 0) {
        $message = "<div class='alert alert-danger'>Invalid input values.</div>";
    } else {
        $conn->begin_transaction();
        try {
            // 1. Check if the product exists in the sale.
            $check_sql = "SELECT quantity FROM sale_items WHERE sale_id = ? AND stock_id = ?";
            $stmt_check = $conn->prepare($check_sql);
            if (!$stmt_check) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_check->bind_param("ii", $sale_id, $stock_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows === 0) throw new Exception("Product not found in this sale.");
            $sale_item = $result->fetch_assoc();
            $sold_quantity = $sale_item['quantity'];

            // 2. Check previous returns.
            // Changed from product_id to stock_id
            $returned_sql = "SELECT SUM(quantity_returned) AS total_returned FROM sales_return WHERE sale_id = ? AND stock_id = ?";
            $stmt_returned = $conn->prepare($returned_sql);
            if (!$stmt_returned) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_returned->bind_param("ii", $sale_id, $stock_id);
            $stmt_returned->execute();
            $returned_result = $stmt_returned->get_result();
            $returned_row = $returned_result->fetch_assoc();
            $previously_returned = $returned_row['total_returned'] ?: 0;
            $remaining_to_return = $sold_quantity - $previously_returned;

            if ($quantity_returned > $remaining_to_return) {
                throw new Exception("The quantity to return ($quantity_returned) exceeds the remaining quantity that can be returned ($remaining_to_return).");
            }

            // 3. Insert into sales_return table.
            // Changed from product_id to stock_id
            $insert_return_sql = "INSERT INTO sales_return (sale_id, stock_id, quantity_returned, return_date) VALUES (?, ?, ?, NOW())";
            $stmt_insert_return = $conn->prepare($insert_return_sql);
            if (!$stmt_insert_return) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_insert_return->bind_param("iii", $sale_id, $stock_id, $quantity_returned);
            $stmt_insert_return->execute();

            // 4. Update stock table.
            $update_stock_sql = "UPDATE stock SET quantity = quantity + ? WHERE id = ?";
            $stmt_update_stock = $conn->prepare($update_stock_sql);
            if (!$stmt_update_stock) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_update_stock->bind_param("ii", $quantity_returned, $stock_id);
            $stmt_update_stock->execute();
            
            $conn->commit();
            $message = "<div class='alert alert-success'>Return processed successfully!</div>";

        } catch (Exception $e) {
            $conn->rollback();
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        } finally {
            if (isset($stmt_check)) $stmt_check->close();
            if (isset($stmt_returned)) $stmt_returned->close();
            if (isset($stmt_insert_return)) $stmt_insert_return->close();
            if (isset($stmt_update_stock)) $stmt_update_stock->close();
        }
    }
}

// Fetch sales IDs for the dropdown
$sales_sql = "SELECT id FROM sales ORDER BY id DESC";
$sales_result = $conn->query($sales_sql);
$sales = [];
if ($sales_result && $sales_result->num_rows > 0) {
    while ($row = $sales_result->fetch_assoc()) {
        $sales[] = $row;
    }
} else {
    $message .= "<div class='alert alert-warning'>No sales found or failed to retrieve sales data.</div>";
}

// Fetch products for the dropdown
// Changed the query to fetch product names from the products table
$products_sql = "SELECT id, product_name FROM products ORDER BY product_name ASC";
$products_result = $conn->query($products_sql);
$products = [];
if ($products_result && $products_result->num_rows > 0) {
    while ($row = $products_result->fetch_assoc()) {
        $products[] = $row;
    }
} else {
    $message .= "<div class='alert alert-warning'>No products found or failed to retrieve product data.</div>";
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
    <?php echo $message; ?>
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title">Process Sales Return</h3>
        </div>
        <div class="card-body">
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