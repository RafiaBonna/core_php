<?php
include_once __DIR__ . '/../../config.php';

$sql = "SELECT s.id, c.customer_name, s.total_amount, s.payment_method, s.sales_date 
        FROM sales s
        LEFT JOIN customers c ON s.customer_id = c.id
        ORDER BY s.sales_date DESC";
$result = $conn->query($sql);
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Sales History</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Sales History</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Sales Transactions</h3>
        </div>
        <div class="card-body">
            <table id="salesTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Customer Name</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name'] ? $row['customer_name'] : 'Guest Customer'); ?></td>
                                <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($row['sales_date'])); ?></td>
                                <td>
                                    <a href="home.php?page=13&sale_id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">View Invoice</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No sales transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>