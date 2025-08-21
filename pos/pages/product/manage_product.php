<?php
include __DIR__ . '/../../config.php';

$sql = "SELECT p.id, p.product_name, p.price, p.stock, p.expiry_date, c.category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id";
$result = $conn->query($sql);
?>

<div class="container my-5">
    <h3>Manage Products</h3>
    <a href="home.php?page=7" class="btn btn-success mb-3">Add New Product</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Total Price</th>
                <th>Stock</th>
                <th>Category</th>
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
                    <td><?php echo htmlspecialchars($row['price']); ?></td>
                    <td><?php echo htmlspecialchars($row['stock']); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['expiry_date'] ? $row['expiry_date'] : 'N/A'); ?></td>
                    <td>
                        <a href="home.php?page=9&id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="pages/product/delete_product.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">No products found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>