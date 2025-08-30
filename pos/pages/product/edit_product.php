<?php
include_once __DIR__ . '/../../config.php';

$product_data = null;
$message = '';
$vendors = [];
$categories = [];

// Fetch all vendors from the database to populate the dropdown
$sql_vendors = "SELECT id, name FROM vendors ORDER BY name ASC";
$result_vendors = $conn->query($sql_vendors);
if ($result_vendors->num_rows > 0) {
    while ($row = $result_vendors->fetch_assoc()) {
        $vendors[] = $row;
    }
}

// Fetch all product categories to populate the dropdown
$sql_categories = "SELECT id, category_name FROM categories ORDER BY category_name ASC";
$result_categories = $conn->query($sql_categories);
if ($result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Check if a product ID is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Fetch the product data from the database
    $sql_fetch = "SELECT * FROM stock WHERE id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $product_id);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        if ($result->num_rows > 0) {
            $product_data = $result->fetch_assoc();
        } else {
            $message = "<div class='alert alert-danger'>Product not found.</div>";
        }
        $stmt_fetch->close();
    } else {
        $message = "<div class='alert alert-danger'>Database error: " . $conn->error . "</div>";
    }
} else {
    $message = "<div class='alert alert-danger'>Invalid product ID.</div>";
}

// Handle form submission for updating the product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $purchase_price = mysqli_real_escape_string($conn, $_POST['purchase_price']);
    $sale_price = mysqli_real_escape_string($conn, $_POST['sale_price']);
    $manufacture_date = !empty($_POST['manufacture_date']) ? mysqli_real_escape_string($conn, $_POST['manufacture_date']) : NULL;
    $expiry_date = !empty($_POST['expiry_date']) ? mysqli_real_escape_string($conn, $_POST['expiry_date']) : NULL;
    $vendor_id = mysqli_real_escape_string($conn, $_POST['vendor_id']);

    $sql_update = "UPDATE stock SET product_name = ?, product_category_id = ?, quantity = ?, purchase_price = ?, sale_price = ?, manufacture_date = ?, expiry_date = ?, vendor_id = ?, updated_at = NOW() WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    if ($stmt_update) {
        // Corrected bind_param string to match 9 variables: string, int, int, double, double, string, string, int, int
        $stmt_update->bind_param("siiddssii", $product_name, $category_id, $quantity, $purchase_price, $sale_price, $manufacture_date, $expiry_date, $vendor_id, $product_id);
        if ($stmt_update->execute()) {
            $message = "<div class='alert alert-success'>Product updated successfully!</div>";
            // To refresh the form with new data, you can refetch the data
            $sql_refetch = "SELECT * FROM stock WHERE id = ?";
            $stmt_refetch = $conn->prepare($sql_refetch);
            if ($stmt_refetch) {
                $stmt_refetch->bind_param("i", $product_id);
                $stmt_refetch->execute();
                $result_refetch = $stmt_refetch->get_result();
                $product_data = $result_refetch->fetch_assoc();
                $stmt_refetch->close();
            }
        } else {
            $message = "<div class='alert alert-danger'>Error updating product: " . $stmt_update->error . "</div>";
        }
        $stmt_update->close();
    } else {
        $message = "<div class='alert alert-danger'>Database error: " . $conn->error . "</div>";
    }
}
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-warning">
                <div class="card-header bg-warning">
                    <h4 class="card-title text-white">Edit Product</h4>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    <?php if ($product_data): ?>
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
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo ($cat['id'] == $product_data['product_category_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="quantity">Quantity</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" value="<?php echo htmlspecialchars($product_data['quantity']); ?>" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="purchase_price">Purchase Price</label>
                                <input type="number" step="0.01" name="purchase_price" id="purchase_price" class="form-control" value="<?php echo htmlspecialchars($product_data['purchase_price']); ?>" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="sale_price">Sale Price</label>
                                <input type="number" step="0.01" name="sale_price" id="sale_price" class="form-control" value="<?php echo htmlspecialchars($product_data['sale_price']); ?>" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="manufacture_date">Manufacture Date</label>
                                <input type="date" name="manufacture_date" id="manufacture_date" class="form-control" value="<?php echo htmlspecialchars($product_data['manufacture_date']); ?>">
                            </div>
                            <div class="form-group mb-3">
                                <label for="expiry_date">Expiry Date</label>
                                <input type="date" name="expiry_date" id="expiry_date" class="form-control" value="<?php echo htmlspecialchars($product_data['expiry_date']); ?>">
                            </div>
                            <div class="form-group mb-4">
                                <label for="vendor_id">Vendor</label>
                                <select name="vendor_id" id="vendor_id" class="form-control" required>
                                    <option value="">Select Vendor</option>
                                    <?php foreach ($vendors as $vendor): ?>
                                        <option value="<?php echo htmlspecialchars($vendor['id']); ?>" <?php echo ($vendor['id'] == $product_data['vendor_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($vendor['name']); ?></option>
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