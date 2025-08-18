<?php
include __DIR__ . '/../../config.php';

$sql = "SELECT c1.id, c1.category_name, c2.category_name AS parent_category_name 
        FROM categories c1 
        LEFT JOIN categories c2 ON c1.parent_id = c2.id";
$result = $conn->query($sql);
?>

<div class="container my-5">
    <h3>Manage Categories</h3>
    <a href="home.php?page=4" class="btn btn-success mb-3">Add New Category</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Parent Category</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['parent_category_name'] ? $row['parent_category_name'] : 'N/A'); ?></td>
                    <td>
                        <a href="home.php?page=6&id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="pages/category/delete_category.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">No categories found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>