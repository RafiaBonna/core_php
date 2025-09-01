<?php
// Include the database connection file.
include_once __DIR__ . '/../../config.php';

// Prepare and execute the query for purchase history.
$sql = "SELECT p.id, v.name AS vendor_name, p.total_amount, p.purchase_date
        FROM purchases p
        LEFT JOIN vendors v ON p.vendor_id = v.id
        ORDER BY p.purchase_date DESC";
$result = $conn->query($sql);
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Purchase History</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Purchase History</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Purchase Transactions</h3>
        </div>
        <div class="card-body">
            <table id="purchaseTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Purchase ID</th>
                        <th>Vendor Name</th>
                        <th>Total Amount</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['vendor_name'] ?: 'N/A'); ?></td>
                                <td><?php echo number_format($row['total_amount'], 2); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($row['purchase_date'])); ?></td>
                                <td>
                                    <a href="home.php?page=18&purchase_id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-info btn-sm">View Invoice</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No purchase transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>