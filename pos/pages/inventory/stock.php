<?php
// Include the database connection file.
include __DIR__ . '/../../config.php';

$message = '';

// Handle stock updates when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $product_id = intval($_POST['product_id']);
    $change_quantity = intval($_POST['change_quantity']);
    $action = $_POST['action']; // 'add' or 'subtract'

    try {
        // Start a transaction for data integrity
        $conn->begin_transaction();

        // Get the current stock from the database
        $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $current_stock = $product['stock'];
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

        // Update the stock in the products table
        $update_stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $new_stock, $product_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Commit the transaction
        $conn->commit();
        $message = "Stock updated successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch all products to display on the page
$sql = "SELECT p.id, p.product_name, p.stock, c.category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id
        ORDER BY p.product_name ASC";
$result = $conn->query($sql);
?>

<div class="container my-5">
    <h3>Stock Management</h3>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Current Stock</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td><?= htmlspecialchars($row['stock']) ?></td>
                        <td>
                            <form method="post" class="d-flex align-items-center">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($row['id']) ?>">
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
                    <td colspan="5" class="text-center">No products found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>