<?php
include __DIR__ . '/../../config.php';

$message = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $message = "<div class='alert alert-success'>Product successfully moved to Expired List.</div>";
    } elseif ($_GET['status'] == 'error' && isset($_GET['message'])) {
        $message = "<div class='alert alert-danger'>Error: " . htmlspecialchars($_GET['message']) . "</div>";
    }
}

// Get products that are expired but still in stock
$sql = "SELECT p.id, p.product_name, p.price, p.stock, p.expiry_date, c.category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.expiry_date < CURDATE() AND p.expiry_date IS NOT NULL AND p.stock > 0";
$result = $conn->query($sql);
?>

<div class="container my-5">
    <h3>Expired Products</h3>
    <?php if ($message) echo $message; ?>
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
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td><?= $row['stock'] ?></td>
                    <td><?= $row['expiry_date'] ?></td>
                    <td>
                        <form method="post" action="pages/product/process_expired_product.php" onsubmit="return confirm('Are you sure you want to move this product to expired list?');">
                            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="stock_to_move" value="<?= $row['stock'] ?>">
                            <input type="hidden" name="expiry_date" value="<?= $row['expiry_date'] ?>">
                            <input type="hidden" name="product_name" value="<?= htmlspecialchars($row['product_name']) ?>">
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
