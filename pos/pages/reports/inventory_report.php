<?php
// Include the database connection file.
include_once __DIR__ . '/../../config.php';

// SQL query to fetch inventory data, including the most recent purchase price.
// The purchase price is fetched from the purchase_items table, as it is not in the products table.
$sql = "SELECT
            p.product_name,
            c.category_name,
            (
                SELECT pi.unit_price
                FROM purchase_items AS pi
                WHERE pi.stock_id = s.id
                ORDER BY pi.created_at DESC
                LIMIT 1
            ) AS purchase_price,
            p.selling_price,
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
            <h3 class="card-title">Current Inventory</h3>
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
                                <td>$<?= number_format($row['purchase_price'], 2) ?></td>
                                <td>$<?= number_format($row['selling_price'], 2) ?></td>
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
