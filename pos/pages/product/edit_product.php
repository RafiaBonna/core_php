<?php
// Include the database connection file.
include_once __DIR__ . '/../../config.php';

$product_data = null;
$message = '';
$categories = [];

// Fetch all product categories to populate the dropdown
$sql_categories = "SELECT id, category_name FROM categories ORDER BY category_name ASC";
$result_categories = $conn->query($sql_categories);
if ($result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Handle product update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    
    // Get product ID from the hidden input field
    $product_id = intval($_POST['product_id']);
    $product_name = trim($_POST['product_name']);
    $category_id = intval($_POST['category_id']);
    
    // Update only the `products` table
    $sql_update_product = "UPDATE products SET product_name = ?, category_id = ? WHERE id = ?";
    $stmt_product = $conn->prepare($sql_update_product);

    if ($stmt_product) {
        $stmt_product->bind_param("sii", $product_name, $category_id, $product_id);
        
        if ($stmt_product->execute()) {
            $message = "<div class='alert alert-success'>Product updated successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error updating product: " . $stmt_product->error . "</div>";
        }
        $stmt_product->close();
    } else {
        $message = "<div class='alert alert-danger'>SQL prepare failed: " . $conn->error . "</div>";
    }
}

// Check if a product ID is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Fetch only the product name and category from the `products` table
    $sql_fetch = "SELECT id, product_name, category_id FROM products WHERE id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $product_id);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        $product_data = $result_fetch->fetch_assoc();
        $stmt_fetch->close();
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit Product</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Edit Product</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Product Details</h3>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    <?php if (!$product_data): ?>
                        <div class="alert alert-danger">Product not found.</div>
                    <?php else: ?>
                        <form action="" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_data['id']); ?>">
                            <div class="form-group mb-3">
                                <label for="product_name">Product Name</label>
                                <input type="text" name="product_name" id="product_name" class="form-control" value="<?php echo htmlspecialchars($product_data['product_name']); ?>" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="category_id">Category</label>
                                <select name="category_id" id="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($category['id'] == $product_data['category_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['category_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="update_product" class="btn btn-warning btn-block">Update Product</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
