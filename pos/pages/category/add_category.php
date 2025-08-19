<?php
include __DIR__ . '/../../config.php';

$error = '';
$success = '';
$category_name = $parent_id = '';

$categories = [];
$cat_sql = "SELECT id, category_name FROM categories WHERE parent_id IS NULL";
$cat_result = $conn->query($cat_sql);
if ($cat_result && $cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) $categories[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name']);
    $parent_id = trim($_POST['parent_id']);

    if ($category_name) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name, parent_id) VALUES (?, ?)");

      if (empty($parent_id)) {
    // If parent_id is empty, insert NULL directly into the SQL query
    $stmt = $conn->prepare("INSERT INTO categories (category_name, parent_id) VALUES (?, NULL)");
    $stmt->bind_param("s", $category_name);
} else {
    // If parent_id is not empty, use the standard prepared statement with 'si'
    $stmt = $conn->prepare("INSERT INTO categories (category_name, parent_id) VALUES (?, ?)");
    $stmt->bind_param("si", $category_name, $parent_id);
}

        if ($stmt->execute()) {
            $success = "New category added successfully!";
            $category_name = $parent_id = '';
        } else {
            $error = $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Category name is required.";
    }
}
?>

<div class="container my-5">
    <h3>Add Category</h3>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="post">
        <input type="text" name="category_name" placeholder="Category Name" class="form-control mb-2" value="<?php echo htmlspecialchars($category_name); ?>" required>
        <select name="parent_id" class="form-select mb-2">
            <option value="">Select Parent Category (Optional)</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>" <?php if($parent_id==$cat['id']) echo "selected"; ?>>
                <?php echo htmlspecialchars($cat['category_name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary w-100">Add Category</button>
    </form>
</div>