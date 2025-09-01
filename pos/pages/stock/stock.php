<?php
// Include the database connection file.
include_once __DIR__ . '/../../config.php';

$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Update Stock ---
    if (isset($_POST['update_stock'])) {
        $stock_id = intval($_POST['stock_id']);
        $change_quantity = intval($_POST['change_quantity']);
        $action = $_POST['action'];

        if ($stock_id <= 0 || $change_quantity <= 0) {
            $message = "<div class='alert alert-danger'>Invalid product or quantity.</div>";
        } else {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("SELECT quantity FROM stock WHERE id = ? FOR UPDATE");
                $stmt->bind_param("i", $stock_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows === 0) throw new Exception("Product stock not found.");
                $stock = $result->fetch_assoc();
                $current_stock = $stock['quantity'];
                $stmt->close();

                $new_stock = ($action === 'add') ? ($current_stock + $change_quantity) : max(0, $current_stock - $change_quantity);

                $update_stmt = $conn->prepare("UPDATE stock SET quantity = ? WHERE id = ?");
                $update_stmt->bind_param("ii", $new_stock, $stock_id);
                $update_stmt->execute();
                $update_stmt->close();

                $conn->commit();
                $message = "<div class='alert alert-success'>Stock updated successfully!</div>";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// Fetch products
$sql = "
    SELECT 
        s.id, 
        p.product_name, 
        c.category_name,
        s.quantity,
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
                <h1 class="m-0">Stock List</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="home.php?page=dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Stock</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid text-center">
    <?php echo $message; ?>
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">All Products Stock</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="stockTable" class="table table-bordered table-striped">
                    <thead class="text-center">
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php
                                $is_expired = false;
                                if (!empty($row['expiry_date']) && strtotime($row['expiry_date']) < time()) {
                                    $is_expired = true;
                                }
                                $row_class = $is_expired ? 'table-danger' : '';
                                ?>
                                <tr class="<?= $row_class ?>">
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                                    <td>
                                        <!-- Changed layout to horizontal with d-flex and a gap -->
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <!-- Update Stock Form -->
                                            <form method="post" class="d-flex align-items-center gap-2">
                                                <input type="hidden" name="stock_id" value="<?= htmlspecialchars($row['id']) ?>">
                                                <select name="action" class="form-select" style="width: auto;">
                                                    <option value="add">Add</option> 
                                                    <option value="subtract">Subtract</option>
                                                </select> &nbsp; &nbsp;
                                                <input type="number" name="change_quantity" class="form-control" value="1" min="1" style="width: 80px;" required>
                                                <button type="submit" name="update_stock" class="btn btn-sm btn-primary">Update</button> &nbsp; &nbsp;
                                            </form>
                                            
                                            <!-- Move to Expired Form -->
                                            <form method="post" action="pages/expired_products/move_to_expired.php">
                                                <input type="hidden" name="stock_id" value="<?= htmlspecialchars($row['id']) ?>">
                                                <input type="hidden" name="quantity" value="<?= htmlspecialchars($row['quantity']) ?>">
                                                <input type="hidden" name="expiry_date" value="<?= htmlspecialchars($row['expiry_date']) ?>">
                                                <button type="submit" name="move_to_expired" class="btn btn-sm btn-warning">Move to Expired</button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
