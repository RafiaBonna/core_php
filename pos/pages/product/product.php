<?php
// Include the database connection file
include_once __DIR__ . '/../../config.php';

$message = '';

// If the 'add_product' form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    
    // Only the product name and category ID are taken
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    
    // SQL query to insert a new product into the products table
    // The 'created_at' column has been removed here
    $sql_insert_product = "INSERT INTO products (product_name, category_id) VALUES (?, ?)";
    $stmt_product = $conn->prepare($sql_insert_product);
    if (!$stmt_product) {
        $message = "<div class='alert alert-danger'>SQL prepare failed: " . $conn->error . "</div>";
    } else {
        $stmt_product->bind_param("si", $product_name, $category_id);
        if ($stmt_product->execute()) {
            $message = "<div class='alert alert-success'>Product added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error adding product: " . $stmt_product->error . "</div>";
        }
        $stmt_product->close();
    }
}

// If 'delete_id' is in the URL, the product deletion code will run
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);

    // Deleting the product from the products table
    $sql_delete_product = "DELETE FROM products WHERE id = ?";
    $stmt_delete_product = $conn->prepare($sql_delete_product);
    if (!$stmt_delete_product) {
        $message = "<div class='alert alert-danger'>SQL prepare failed: " . $conn->error . "</div>";
    } else {
        $stmt_delete_product->bind_param("i", $delete_id);
        if ($stmt_delete_product->execute()) {
            $message = "<div class='alert alert-success'>Product deleted successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error deleting product: " . $stmt_delete_product->error . "</div>";
        }
        $stmt_delete_product->close();
    }
}

// Fetching all category data from the database for the dropdown menu
$sql_categories = "SELECT id, category_name FROM categories ORDER BY category_name ASC";
$result_categories = $conn->query($sql_categories);
$categories = [];
if ($result_categories && $result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetching all product information from the database to display in the table
$sql_products = "
    SELECT 
        p.id, 
        p.product_name, 
        c.category_name 
    FROM products AS p
    LEFT JOIN categories AS c ON p.category_id = c.id
    ORDER BY p.id DESC
";
$result_products = $conn->query($sql_products);
$products = [];
if ($result_products && $result_products->num_rows > 0) {
    while ($row = $result_products->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
</head>
<body>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h1 class="m-0">Manage Products</h1>
        </div>
    </div>
    <?php echo $message; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Add New Product</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="product_name">Product Name</label>
                            <input type="text" name="product_name" id="product_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" name="add_product" class="btn btn-primary btn-block">Add Product</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Products</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="productsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center">Product Name</th>
                            <th class="text-center">Category</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($product['id']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td class="d-flex justify-content-center">
                                        <a href="home.php?page=11&id=<?php echo htmlspecialchars($product['id']); ?>" class="btn btn-sm btn-info">Edit</a>&nbsp;
                                        <a href="home.php?page=9&delete_id=<?php echo htmlspecialchars($product['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>