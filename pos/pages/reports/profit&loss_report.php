<?php
// Ensure the session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection file
include_once __DIR__ . '/../../config.php';

$total_sales = 0;
$total_purchases = 0;
$profit_loss = 0;

// Check the database connection
if (isset($conn)) {
    // Query to get total sales amount
    $sales_sql = "SELECT SUM(total_amount) AS total_sales FROM sales";
    $sales_result = $conn->query($sales_sql);
    if ($sales_result && $sales_result->num_rows > 0) {
        $row = $sales_result->fetch_assoc();
        $total_sales = $row['total_sales'] ?? 0;
    }

    // Query to get total purchases amount
    $purchases_sql = "SELECT SUM(total_amount) AS total_purchases FROM purchases";
    $purchases_result = $conn->query($purchases_sql);
    if ($purchases_result && $purchases_result->num_rows > 0) {
        $row = $purchases_result->fetch_assoc();
        $total_purchases = $row['total_purchases'] ?? 0;
    }

    // Calculate profit or loss
    $profit_loss = $total_sales - $total_purchases;
} else {
    // Error message if connection is not found
    $_SESSION['error_message'] = "Database connection not found.";
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Profit & Loss Report</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="home.php?page=dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Profit & Loss Report</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Profit & Loss Overview</h3>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                </div>
            <?php unset($_SESSION['error_message']); endif; ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Sales Revenue</span>
                            <span class="info-box-number">$<?php echo number_format($total_sales, 2); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-gradient-danger">
                        <span class="info-box-icon"><i class="fas fa-shopping-basket"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Purchase Cost</span>
                            <span class="info-box-number">$<?php echo number_format($total_purchases, 2); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box <?php echo ($profit_loss >= 0) ? 'bg-gradient-info' : 'bg-gradient-warning'; ?>">
                        <span class="info-box-icon"><i class="fas fa-hand-holding-usd"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Gross <?php echo ($profit_loss >= 0) ? 'Profit' : 'Loss'; ?></span>
                            <span class="info-box-number">$<?php echo number_format($profit_loss, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
