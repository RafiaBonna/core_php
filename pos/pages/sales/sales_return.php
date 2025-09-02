<?php
// Include the database connection file.
include_once __DIR__ . '/../../config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_return'])) {
    $sale_id = intval($_POST['sale_id']);
    $product_id_from_form = intval($_POST['product_id']); 
    $quantity_returned = intval($_POST['quantity_returned']);
    $reason = trim($_POST['reason']);

    if ($sale_id <= 0 || $product_id_from_form <= 0 || $quantity_returned <= 0) {
        $message = "<div class='alert alert-danger'>Invalid input values.</div>";
    } else {
        $conn->begin_transaction();
        try {
            // 1) Find the sale item details
            $check_sql = "SELECT 
                            si.quantity,
                            si.unit_price,                  
                            si.id AS sale_item_id,
                            s.id  AS stock_id
                          FROM sale_items AS si
                          JOIN stock    AS s ON si.stock_id = s.id
                          JOIN products AS p ON s.product_id = p.id
                          WHERE si.sale_id = ? AND p.id = ?";

            $stmt_check = $conn->prepare($check_sql);
            if (!$stmt_check) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_check->bind_param("ii", $sale_id, $product_id_from_form);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows === 0) throw new Exception("Product not found in this sale.");

            $sale_item      = $result->fetch_assoc();
            $sold_quantity  = (int)$sale_item['quantity'];
            $sold_price     = (float)$sale_item['unit_price'];
            $stock_id       = (int)$sale_item['stock_id'];

            // 2) Check previous returns
            $check_returns_sql = "SELECT COALESCE(SUM(quantity),0) AS total_returned 
                                  FROM return_items 
                                  WHERE sale_id = ? AND stock_id = ?";
            $stmt_returns = $conn->prepare($check_returns_sql);
            if (!$stmt_returns) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_returns->bind_param("ii", $sale_id, $stock_id);
            $stmt_returns->execute();
            $returns_result = $stmt_returns->get_result();
            $total_returned = (int)($returns_result->fetch_assoc()['total_returned'] ?? 0);

            if (($total_returned + $quantity_returned) > $sold_quantity) {
                throw new Exception("Cannot return more quantity than was sold.");
            }

            // 3) Insert into return_items table
            $insert_sql = "INSERT INTO return_items (sale_id, stock_id, quantity, unit_price, return_date, reason) 
                           VALUES (?, ?, ?, ?, NOW(), ?)";
            $stmt_insert = $conn->prepare($insert_sql);
            if (!$stmt_insert) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_insert->bind_param("iiids", $sale_id, $stock_id, $quantity_returned, $sold_price, $reason);
            $stmt_insert->execute();

            // 4) Add returned quantity back to stock
            $update_stock_sql = "UPDATE stock SET quantity = quantity + ? WHERE id = ?";
            $stmt_update_stock = $conn->prepare($update_stock_sql);
            if (!$stmt_update_stock) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_update_stock->bind_param("ii", $quantity_returned, $stock_id);
            $stmt_update_stock->execute();

            // 5) Reduce the sale total
            $reduction_amount = $quantity_returned * $sold_price;
            $update_sale_sql = "UPDATE sales SET total_amount = total_amount - ? WHERE id = ?";
            $stmt_update_sale = $conn->prepare($update_sale_sql);
            if (!$stmt_update_sale) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_update_sale->bind_param("di", $reduction_amount, $sale_id);
            $stmt_update_sale->execute();

            $conn->commit();
            $message = "<div class='alert alert-success'>Sale return processed successfully!</div>";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Dropdown data
$sales_sql = "SELECT id FROM sales ORDER BY id DESC";
$sales = $conn->query($sales_sql)->fetch_all(MYSQLI_ASSOC);

$products_sql = "SELECT id, product_name FROM products ORDER BY product_name ASC";
$products = $conn->query($products_sql)->fetch_all(MYSQLI_ASSOC);
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Sales Return</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="home.php?page=dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Sales Return</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Process a Sales Return</h3></div>
        <div class="card-body">
            <?= $message; ?>
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

                <div class="mb-3">
                    <label for="reason" class="form-label">Reason for Return</label>
                    <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                </div>

                <button type="submit" name="process_return" class="btn btn-danger w-100">Process Return</button>
            </form>
        </div>
    </div>
</div>
