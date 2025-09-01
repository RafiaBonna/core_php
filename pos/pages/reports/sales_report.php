<?php
// Ensure the session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Including the config.php file
include_once __DIR__ . '/../../config.php';

$result = null;

// Checking the database connection
if (isset($conn)) {
    // Query to fetch sales report data
    $sql = "SELECT s.id, c.name AS customer_name, s.total_amount, s.sale_date
            FROM sales s
            LEFT JOIN customers c ON s.customer_id = c.id
            ORDER BY s.sale_date DESC";

    $result = $conn->query($sql);

    // Handling the error if the query fails
    if ($result === false) {
        $_SESSION['error_message'] = "Failed to fetch sales report: " . $conn->error;
        $result = null;
    }
} else {
    // Error message if connection is not found
    $_SESSION['error_message'] = "Database connection not found.";
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Sales Report</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="home.php?page=dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Sales Report</li>
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
            <?php
            // Displaying the error message if there is one
            if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                </div>
            <?php unset($_SESSION['error_message']); endif; ?>

            <table id="salesTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Customer Name</th>
                        <th>Total Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name'] ?: 'N/A'); ?></td>
                                <td><?php echo number_format($row['total_amount'], 2); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($row['sale_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No sales transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
