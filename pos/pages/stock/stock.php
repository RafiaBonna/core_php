<?php
// Include the database connection file.
include_once __DIR__ . '/../../config.php';

$message = '';

// Handle stock updates when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $stock_id = intval($_POST['stock_id']);
    $change_quantity = intval($_POST['change_quantity']);
    $action = $_POST['action']; // 'add' or 'subtract'

    if ($stock_id <= 0 || $change_quantity <= 0) {
        $message = "<div class='alert alert-danger'>Invalid product or quantity.</div>";
    } else {
        $conn->begin_transaction();
        try {
            // Get the current stock from the database using the stock table
            $stmt = $conn->prepare("SELECT quantity FROM stock WHERE id = ? FOR UPDATE");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("i", $stock_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) throw new Exception("Product stock not found.");
            $stock = $result->fetch_assoc();
            $current_stock = $stock['quantity'];
            $stmt->close();

            // Calculate the new stock based on the action
            if ($action === 'add') {
                $new_stock = $current_stock + $change_quantity;
            } else { // 'subtract'
                $new_stock = $current_stock - $change_quantity;
                if ($new_stock < 0) {
                    $new_stock = 0; // Prevent negative stock
                }
            }

            // Update the quantity in the stock table
            $update_stmt = $conn->prepare("UPDATE stock SET quantity = ? WHERE id = ?");
            if (!$update_stmt) throw new Exception("Prepare failed: " . $conn->error);
            $update_stmt->bind_param("ii", $new_stock, $stock_id);
            $update_stmt->execute();
            $update_stmt->close();

            // Commit the transaction
            $conn->commit();
            $message = "<div class='alert alert-success'>Stock updated successfully!</div>";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Fetch all products to display on the page by joining products and stock tables
$sql = "
    SELECT 
        s.id, 
        p.product_name, 
        c.category_name,
        s.quantity,
        s.purchase_price,
        s.sale_price,
        s.manufacture_date,
        s.expiry_date
    FROM stock AS s
    LEFT JOIN products AS p ON s.product_id = p.id
    LEFT JOIN categories AS c ON p.category_id = c.id
    ORDER BY p.product_name ASC
";
$result = $conn->query($sql);

?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Stock Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Stock</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php echo $message; ?>
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">All Products Stock</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="stockTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Purchase Price</th>
                            <th>Sale Price</th>
                            <th>Manufacture Date</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                                    <td><?= htmlspecialchars($row['purchase_price']) ?></td>
                                    <td><?= htmlspecialchars($row['sale_price']) ?></td>
                                    <td><?= htmlspecialchars($row['manufacture_date']) ?></td>
                                    <td><?= htmlspecialchars($row['expiry_date']) ?></td>
                                    <td>
                                        <form method="post" class="d-flex align-items-center">
                                            <input type="hidden" name="stock_id" value="<?= htmlspecialchars($row['id']) ?>">
                                            <select name="action" class="form-select me-2" style="width: auto;">
                                                <option value="add">Add</option>
                                                <option value="subtract">Subtract</option>
                                            </select>
                                            <input type="number" name="change_quantity" class="form-control me-2" value="1" min="1" style="width: 80px;" required>
                                            <button type="submit" name="update_stock" class="btn btn-sm btn-primary">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>