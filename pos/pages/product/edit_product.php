<?php
include __DIR__ . '/../../config.php';

$product_name = $price = $stock = $category_id = $id = $expiry_date = '';
$message = "";

$categories = [];
$cat_sql = "SELECT id, category_name FROM categories";
$cat_result = $conn->query($cat_sql);
if ($cat_result && $cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) $categories[] = $row;
}

if (isset($_POST["btnUpdate"])) {
    $id = $_POST["id"];
    $product_name = trim($_POST["product_name"]);
    $price = trim($_POST["price"]);
    $stock = trim($_POST["stock"]);
    $category_id = trim($_POST["category_id"]);
    $expiry_date = empty($_POST['expiry_date']) ? NULL : $_POST['expiry_date'];

    if ($product_name && $price && $stock && $category_id) {
        $stmt = $conn->prepare("UPDATE products SET product_name=?, price=?, stock=?, category_id=?, expiry_date=? WHERE id=?");
        $stmt->bind_param("sdiisi", $product_name, $price, $stock, $category_id, $expiry_date, $id);

        if ($stmt->execute()) {
            $message = "Product updated successfully!";
        } else {
            $message = "Error updating record: " . $conn->error;
        }
        $stmt->close();
    } else {
        $message = "All fields are required.";
    }
} else if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    $stmt = $conn->prepare("SELECT product_name, price, stock, category_id, expiry_date FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $product_name = $row['product_name'];
        $price = $row['price'];
        $stock = $row['stock'];
        $category_id = $row['category_id'];
        $expiry_date = $row['expiry_date'];
    } else {
        $message = "No product found with that ID.";
    }
    $stmt->close();
}
?>

<div class="container my-5">
    <h3>Update Product</h3>
    <div class="ftitle text-center">
        <h4><?php echo $message ? $message : "Product Update Form" ?></h4>
    </div>
    <form action="?page=9&id=<?php echo $id; ?>" method="post">
        <div class="form-group">
            <input type="hidden" name="id" value="<?php echo $id ?>">
        </div>
        <div class="form-group">
            <label for="product_name">Product Name</label>
            <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product_name); ?>">
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>">
        </div>
        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" class="form-control" id="stock" name="stock" value="<?php echo htmlspecialchars($stock); ?>">
        </div>
        <div class="form-group">
            <label for="expiry_date">Expiry Date</label>
            <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="<?php echo htmlspecialchars($expiry_date); ?>">
        </div>
        <div class="form-group">
            <label for="category_id">Category</label>
            <select name="category_id" class="form-select mb-2">
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php if($category_id==$cat['id']) echo "selected"; ?>>
                    <?php echo htmlspecialchars($cat['category_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" name="btnUpdate">Update Product</button>
    </form>
</div>