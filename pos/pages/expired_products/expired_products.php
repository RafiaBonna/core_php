<?php
// Include the database connection file.
include_once __DIR__ . '/../../config.php';

// Fetch expired products with proper JOINs (expired_products → stock → products).
$sql = "SELECT ep.id, p.product_name, ep.quantity_expired, ep.expiry_date 
        FROM expired_products AS ep
        JOIN stock AS s ON ep.stock_id = s.id
        JOIN products AS p ON s.product_id = p.id
        ORDER BY ep.expiry_date DESC";

$result = $conn->query($sql);

if (!$result) {
    // Error message for debugging
    die("Error fetching expired products: " . $conn->error);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Expired Products</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="home.php?page=dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Expired Products</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Expired Products</h3>
        </div>
        <div class="card-body">
            <table id="expiredProductsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Expired Quantity</th>
                        <th>Expiry Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['quantity_expired']); ?></td>
                                <td><?php echo htmlspecialchars($row['expiry_date']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No expired products found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
