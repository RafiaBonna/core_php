<?php
// Include the database connection file
include_once __DIR__ . '/../../config.php';

// Handle adding a new product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    
    // Get and sanitize form data
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $purchase_price = mysqli_real_escape_string($conn, $_POST['purchase_price']);
    $sale_price = mysqli_real_escape_string($conn, $_POST['sale_price']);
    $manufacture_date = !empty($_POST['manufacture_date']) ? mysqli_real_escape_string($conn, $_POST['manufacture_date']) : NULL;
    $expiry_date = !empty($_POST['expiry_date']) ? mysqli_real_escape_string($conn, $_POST['expiry_date']) : NULL;
    $vendor_id = mysqli_real_escape_string($conn, $_POST['vendor_id']);
    
    // SQL query to insert new product into the stock table
    $sql_insert = "INSERT INTO stock (product_name, product_category_id, quantity, purchase_price, sale_price, manufacture_date, expiry_date, vendor_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    // Prepare and bind parameters to prevent SQL injection
    $stmt = $conn->prepare($sql_insert);
    // Corrected bind_param string to match 8 variables: string, int, int, double, double, string, string, int
    $stmt->bind_param("siiddssi", $product_name, $category_id, $quantity, $purchase_price, $sale_price, $manufacture_date, $expiry_date, $vendor_id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Product added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error adding product: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

// Handle deleting a product
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);

    $sql_delete = "DELETE FROM stock WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $delete_id);

    if ($stmt_delete->execute()) {
        echo "<div class='alert alert-success'>Product deleted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error deleting product: " . $stmt_delete->error . "</div>";
    }
    $stmt_delete->close();
}


// Fetch all vendors from the database to populate the dropdown
$sql_vendors = "SELECT id, name FROM vendors ORDER BY name ASC";
$result_vendors = $conn->query($sql_vendors);
$vendors = [];
if ($result_vendors->num_rows > 0) {
    while ($row = $result_vendors->fetch_assoc()) {
        $vendors[] = $row;
    }
}

// FIX: Corrected table name from 'product_categories' to 'categories'
// Fetch all product categories to populate the dropdown
$sql_categories = "SELECT id, category_name FROM categories ORDER BY category_name ASC";
$result_categories = $conn->query($sql_categories);
$categories = [];
if ($result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

// FIX: Corrected table name from 'product_categories' to 'categories' in the LEFT JOIN
// Fetch all products from the database to display in the table
$sql_products = "SELECT s.id, s.product_name, s.quantity, s.sale_price, s.purchase_price, pc.category_name, v.name AS vendor_name, s.manufacture_date, s.expiry_date FROM stock AS s LEFT JOIN categories AS pc ON s.product_category_id = pc.id LEFT JOIN vendors AS v ON s.vendor_id = v.id ORDER BY s.id DESC";
$result_products = $conn->query($sql_products);
$products = [];
if ($result_products->num_rows > 0) {
    while ($row = $result_products->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header bg-primary">
                    <h4 class="card-title text-white">Add New Product</h4>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="form-group mb-3">
                            <label for="product_name">Product Name</label>
                            <input type="text" name="product_name" id="product_name" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['id']); ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="quantity">Quantity</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="purchase_price">Purchase Price</label>
                            <input type="number" step="0.01" name="purchase_price" id="purchase_price" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="sale_price">Sale Price</label>
                            <input type="number" step="0.01" name="sale_price" id="sale_price" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="manufacture_date">Manufacture Date</label>
                            <input type="date" name="manufacture_date" id="manufacture_date" class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" name="expiry_date" id="expiry_date" class="form-control">
                        </div>
                        <div class="form-group mb-4">
                            <label for="vendor_id">Vendor</label>
                            <select name="vendor_id" id="vendor_id" class="form-control" required>
                                <option value="">Select Vendor</option>
                                <?php foreach ($vendors as $vendor): ?>
                                    <option value="<?php echo htmlspecialchars($vendor['id']); ?>"><?php echo htmlspecialchars($vendor['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="add_product" class="btn btn-primary btn-block">Add Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

    <div class="card card-info">
        <div class="card-header bg-info">
            <h4 class="card-title text-white">Manage Products</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Vendor</th>
                            <th>Quantity</th>
                            <th>Purchase Price</th>
                            <th>Sale Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['vendor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($product['purchase_price']); ?></td>
                                    <td><?php echo htmlspecialchars($product['sale_price']); ?></td>
                                    <td class="d-flex justify-content-center">
                                        <a href="home.php?page=9&id=<?php echo htmlspecialchars($product['id']); ?>" class="btn btn-sm btn-info me-2">Edit</a>
                                        <a href="home.php?page=7&delete_id=<?php echo htmlspecialchars($product['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>