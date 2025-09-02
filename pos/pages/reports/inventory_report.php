<?php
// Include the database connection file.
include_once __DIR__ . '/../../config.php';

// SQL query to fetch inventory data directly from the 'stock' table.
// The query is simplified to use the 'purchase_price' and 'sale_price' columns
// that are already present in the 'stock' table, as per the database schema.
$sql = "SELECT
            p.product_name,
            c.category_name,
            s.purchase_price,
            s.sale_price,
            s.quantity AS stock_quantity,
            s.expiry_date
        FROM
            products AS p
        JOIN
            stock AS s ON p.id = s.product_id
        JOIN
            categories AS c ON p.category_id = c.id
        ORDER BY
            p.product_name ASC";

$result = $conn->query($sql);

// Check if the query executed successfully
if (!$result) {
    die("Error fetching inventory data: " . $conn->error);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Inventory Report</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="home.php?page=dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Inventory Report</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Products in Stock</h3>
        </div>
        <div class="card-body">
            <table id="inventoryTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Purchase Price</th>
                        <th>Selling Price</th>
                        <th>Stock Quantity</th>
                        <th>Expiry Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td><?= htmlspecialchars($row['category_name']) ?></td>
                                <td><?= htmlspecialchars($row['purchase_price']) ?></td>
                                <td><?= htmlspecialchars($row['sale_price']) ?></td>
                                <td><?= htmlspecialchars($row['stock_quantity']) ?></td>
                                <td><?= htmlspecialchars($row['expiry_date']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No products found in the inventory.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
