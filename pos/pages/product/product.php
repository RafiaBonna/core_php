<?php
// Database connection file টি সংযুক্ত করা হচ্ছে
include_once __DIR__ . '/../../config.php';

$message = '';

// যদি 'add_product' ফর্ম সাবমিট করা হয়
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    
    // শুধুমাত্র পণ্যের নাম এবং ক্যাটাগরি আইডি নেওয়া হচ্ছে
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    
    // নতুন পণ্য products টেবিলে ইনসার্ট করার জন্য SQL কোয়েরি
    // এখানে 'created_at' কলামটি বাদ দেওয়া হয়েছে
    $sql_insert_product = "INSERT INTO products (product_name, category_id) VALUES (?, ?)";
    $stmt_product = $conn->prepare($sql_insert_product);
    if (!$stmt_product) {
        $message = "<div class='alert alert-danger'>SQL prepare failed: " . $conn->error . "</div>";
    } else {
        $stmt_product->bind_param("si", $product_name, $category_id);
        if ($stmt_product->execute()) {
            $message = "<div class='alert alert-success'>পণ্য সফলভাবে যুক্ত করা হয়েছে!</div>";
        } else {
            $message = "<div class='alert alert-danger'>পণ্য যুক্ত করার সময় ত্রুটি: " . $stmt_product->error . "</div>";
        }
        $stmt_product->close();
    }
}

// যদি 'delete_id' URL-এ থাকে, তবে পণ্য মুছে ফেলার কোড রান করবে
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);

    // products টেবিল থেকে পণ্যটি মুছে ফেলা হচ্ছে
    $sql_delete_product = "DELETE FROM products WHERE id = ?";
    $stmt_delete_product = $conn->prepare($sql_delete_product);
    if (!$stmt_delete_product) {
        $message = "<div class='alert alert-danger'>SQL prepare failed: " . $conn->error . "</div>";
    } else {
        $stmt_delete_product->bind_param("i", $delete_id);
        if ($stmt_delete_product->execute()) {
            $message = "<div class='alert alert-success'>পণ্য সফলভাবে মুছে ফেলা হয়েছে!</div>";
        } else {
            $message = "<div class='alert alert-danger'>পণ্য মুছে ফেলার সময় ত্রুটি: " . $stmt_delete_product->error . "</div>";
        }
        $stmt_delete_product->close();
    }
}

// ড্রপডাউন মেনুর জন্য ডেটাবেজ থেকে সমস্ত ক্যাটাগরি ডেটা আনা হচ্ছে
$sql_categories = "SELECT id, category_name FROM categories ORDER BY category_name ASC";
$result_categories = $conn->query($sql_categories);
$categories = [];
if ($result_categories && $result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

// টেবিলে দেখানোর জন্য ডেটাবেজ থেকে সমস্ত পণ্যের তথ্য আনা হচ্ছে
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
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td class="d-flex justify-content-center">
                                        <a href="home.php?page=9&id=<?php echo htmlspecialchars($product['id']); ?>" class="btn btn-sm btn-info me-2">Edit</a>
                                        <a href="home.php?page=7&delete_id=<?php echo htmlspecialchars($product['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
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