<?php
include_once __DIR__ . '/../../config.php';

$product_data = null;
$message = '';

// Check if a product ID is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Fetch the product data from the database
    $sql_fetch = "SELECT * FROM products WHERE id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
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
    $message = "<div class='alert alert-danger'>Invalid product ID.</div>";
}

// Handle form submission for updating the product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);

    $sql_update = "UPDATE products SET product_name = ?, price = ?, stock = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sdis", $product_name, $price, $stock, $product_id);

    if ($stmt_update->execute()) {
        $message = "<div class='alert alert-success'>Product updated successfully!</div>";
        // To refresh the form with new data, you can refetch the data
        $sql_refetch = "SELECT * FROM products WHERE id = ?";
        $stmt_refetch = $conn->prepare($sql_refetch);
        $stmt_refetch->bind_param("i", $product_id);
        $stmt_refetch->execute();
        $result_refetch = $stmt_refetch->get_result();
        $product_data = $result_refetch->fetch_assoc();
        $stmt_refetch->close();
    } else {
        $message = "<div class='alert alert-danger'>Error updating product: " . $stmt_update->error . "</div>";
    }
    $stmt_update->close();
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
                                <label for="price">Price</label>
                                <input type="number" name="price" id="price" class="form-control" step="0.01" value="<?php echo htmlspecialchars($product_data['price']); ?>" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="stock">Stock</label>
                                <input type="number" name="stock" id="stock" class="form-control" value="<?php echo htmlspecialchars($product_data['stock']); ?>" required>
                            </div>
                            <button type="submit" name="update_product" class="btn btn-warning btn-block">Update Product</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>