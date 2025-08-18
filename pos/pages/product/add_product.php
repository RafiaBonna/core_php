<?php
include __DIR__ . '/../../config.php';

$error = '';
$success = '';
$product_name = $price = $stock = $category_id = '';

$categories = [];
$cat_sql = "SELECT id, category_name FROM categories";
$cat_result = $conn->query($cat_sql);
if ($cat_result && $cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) $categories[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    $category_id = trim($_POST['category_id']);

    if ($product_name && $price && $stock && $category_id) {
        $stmt = $conn->prepare("INSERT INTO products (product_name, price, stock, category_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdii", $product_name, $price, $stock, $category_id);

        if ($stmt->execute()) {
            $success = "New product added successfully!";
            $product_name = $price = $stock = $category_id = '';
        } else {
            $error = $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "All fields are required.";
    }
}
?>

<div class="container my-5">
    <h3>Add Product</h3>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="post">
        <input type="text" name="product_name" placeholder="Product Name" class="form-control mb-2" value="<?php echo htmlspecialchars($product_name); ?>" required>
        <input type="number" name="price" step="0.01" placeholder="Price" class="form-control mb-2" value="<?php echo htmlspecialchars($price); ?>" required>
        <input type="number" name="stock" placeholder="Stock" class="form-control mb-2" value="<?php echo htmlspecialchars($stock); ?>" required>
        <select name="category_id" class="form-select mb-2" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>" <?php if($category_id==$cat['id']) echo "selected"; ?>>
                <?php echo htmlspecialchars($cat['category_name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary w-100">Add Product</button>
    </form>
</div>