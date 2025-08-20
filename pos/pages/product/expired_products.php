<?php
include __DIR__ . '/../../config.php';

// Check if there's a request to move products
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['move_to_expired'])) {
    $product_id = intval($_POST['product_id']);
    $stock_to_move = intval($_POST['stock_to_move']);
    $expiry_date = $_POST['expiry_date'];
    $product_name = $_POST['product_name'];
    
    // Start a transaction
    $conn->begin_transaction();

    try {
        // 1. Insert into expired_products table
        $stmt_insert = $conn->prepare("INSERT INTO expired_products (product_id, product_name, quantity_expired, expiry_date) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("isis", $product_id, $product_name, $stock_to_move, $expiry_date);
        
        if (!$stmt_insert->execute()) {
            throw new Exception("Error inserting into expired_products: " . $stmt_insert->error);
        }
        $stmt_insert->close();

        // 2. Delete from products table
        // We delete the product completely, as its entire stock is expired.
        $stmt_delete = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt_delete->bind_param("i", $product_id);
        
        if (!$stmt_delete->execute()) {
            throw new Exception("Error deleting from products: " . $stmt_delete->error);
        }
        $stmt_delete->close();
        
        $conn->commit();
        $message = "<div class='alert alert-success'>Product successfully moved to Expired List.</div>";

    } catch (Exception $e) {
        $conn->rollback();
        $message = "<div class='alert alert-danger'>Error moving product: " . $e->getMessage() . "</div>";
    }
}

// Fetch products that are expired
$sql = "SELECT p.id, p.product_name, p.price, p.stock, p.expiry_date, c.category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id
        WHERE p.expiry_date < CURDATE() AND p.expiry_date IS NOT NULL";
$result = $conn->query($sql);
?>

<div class="container my-5">
    <h3>Expired Products</h3>
    <?php if (isset($message)) echo $message; ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Current Stock</th>
                <th>Expiry Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['stock']); ?></td>
                    <td><?php echo htmlspecialchars($row['expiry_date']); ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Are you sure you want to move this product to expired list? This will remove it from your main stock.');">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="stock_to_move" value="<?php echo $row['stock']; ?>">
                            <input type="hidden" name="expiry_date" value="<?php echo $row['expiry_date']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['product_name']); ?>">
                            <button type="submit" name="move_to_expired" class="btn btn-warning btn-sm">Move to Expired</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No expired products found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>