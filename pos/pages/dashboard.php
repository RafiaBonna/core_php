<?php
// Include the database connection file
include 'config.php';

// Initialize variables with default values to prevent errors
$today_orders = 0;
$today_sales_revenue = 0;
$total_products = 0;
$out_of_stock_products = 0;

// Fetch total number of orders today from the 'sales' table
$sql_today_orders = "SELECT COUNT(*) AS total FROM sales WHERE DATE(sales_date) = CURDATE()";
$result_orders = $conn->query($sql_today_orders);
if ($result_orders && $result_orders->num_rows > 0) {
    $row = $result_orders->fetch_assoc();
    $today_orders = $row['total'];
}

// Fetch total sales revenue today from the 'sales' table
$sql_today_sales_revenue = "SELECT SUM(total_amount) AS total_revenue FROM sales WHERE DATE(sales_date) = CURDATE()";
$result_revenue = $conn->query($sql_today_sales_revenue);
if ($result_revenue && $result_revenue->num_rows > 0) {
    $row = $result_revenue->fetch_assoc();
    $today_sales_revenue = number_format($row['total_revenue'], 2) ?? '0.00';
}

// Fetch total number of products from the 'stock' table
$sql_total_products = "SELECT COUNT(*) AS total FROM stock";
$result_products = $conn->query($sql_total_products);
if ($result_products && $result_products->num_rows > 0) {
    $row = $result_products->fetch_assoc();
    $total_products = $row['total'];
}

// Fetch number of products with quantity below a certain threshold (e.g., 5)
$sql_out_of_stock = "SELECT COUNT(*) AS total FROM stock WHERE quantity <= 5";
$result_out_of_stock = $conn->query($sql_out_of_stock);
if ($result_out_of_stock && $result_out_of_stock->num_rows > 0) {
    $row = $result_out_of_stock->fetch_assoc();
    $out_of_stock_products = $row['total'];
}

// Fetch data for the bar chart (last 7 days sales)
$bar_chart_labels = [];
$bar_chart_data = [];
$sql_bar_chart = "SELECT DATE(sales_date) AS sale_date, SUM(total_amount) AS daily_revenue FROM sales WHERE sales_date >= CURDATE() - INTERVAL 6 DAY GROUP BY sale_date ORDER BY sale_date ASC";
$result_bar_chart = $conn->query($sql_bar_chart);
if ($result_bar_chart && $result_bar_chart->num_rows > 0) {
    while ($row = $result_bar_chart->fetch_assoc()) {
        $bar_chart_labels[] = "'" . date('M d', strtotime($row['sale_date'])) . "'";
        $bar_chart_data[] = $row['daily_revenue'];
    }
}
$bar_chart_labels_js = implode(', ', $bar_chart_labels);
$bar_chart_data_js = implode(', ', $bar_chart_data);

// Fetch data for the pie chart (top 5 selling products by quantity)
$pie_chart_labels = [];
$pie_chart_data = [];
$sql_pie_chart = "SELECT s.product_name, SUM(si.quantity) AS total_quantity FROM sales_items si JOIN stock s ON si.product_id = s.id GROUP BY s.product_name ORDER BY total_quantity DESC LIMIT 5";
$result_pie_chart = $conn->query($sql_pie_chart);
if ($result_pie_chart && $result_pie_chart->num_rows > 0) {
    while ($row = $result_pie_chart->fetch_assoc()) {
        $pie_chart_labels[] = "'" . htmlspecialchars($row['product_name']) . "'";
        $pie_chart_data[] = $row['total_quantity'];
    }
}
$pie_chart_labels_js = implode(', ', $pie_chart_labels);
$pie_chart_data_js = implode(', ', $pie_chart_data);

// Fetch recent sales for the table
$recent_sales = [];
$sql_recent_sales = "SELECT s.id, s.sales_date, c.customer_name, s.total_amount, s.status FROM sales s LEFT JOIN customers c ON s.customer_id = c.id ORDER BY s.sales_date DESC LIMIT 5";
$result_recent_sales = $conn->query($sql_recent_sales);
if ($result_recent_sales && $result_recent_sales->num_rows > 0) {
    while ($row = $result_recent_sales->fetch_assoc()) {
        $recent_sales[] = $row;
    }
}
?>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?php echo htmlspecialchars($today_orders); ?></h3>
                <p>Today's Orders</p>
            </div>
            <div class="icon"><i class="ion ion-bag"></i></div>
            <a href="home.php?page=reports_sales" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>৳<?php echo htmlspecialchars($today_sales_revenue); ?></h3>
                <p>Today's Sales</p>
            </div>
            <div class="icon"><i class="ion ion-stats-bars"></i></div>
            <a href="home.php?page=reports_sales" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?php echo htmlspecialchars($total_products); ?></h3>
                <p>Total Products</p>
            </div>
            <div class="icon"><i class="fas fa-boxes"></i></div>
            <a href="home.php?page=7" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?php echo htmlspecialchars($out_of_stock_products); ?></h3>
                <p>Low Stock</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
            <a href="home.php?page=7" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <section class="col-lg-7 connectedSortable">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Last 7 Days Sales</h3>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </section>
    <section class="col-lg-5 connectedSortable">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">Top Selling Products</h3>
            </div>
            <div class="card-body">
                <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </section>
</div>

<div class="row">
    <section class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Sales</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_sales) > 0): ?>
                            <?php foreach ($recent_sales as $sale): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sale['id']); ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($sale['sales_date']))); ?></td>
                                    <td><?php echo htmlspecialchars($sale['customer_name'] ?? 'N/A'); ?></td>
                                    <td>৳<?php echo htmlspecialchars(number_format($sale['total_amount'], 2)); ?></td>
                                    <td>
                                        <span class="badge bg-success"><?php echo htmlspecialchars($sale['status']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No recent sales found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script src="dist/plugins/jquery/jquery.min.js"></script>
<script src="dist/plugins/chart.js/Chart.min.js"></script>

<script>
    $(function () {
        // BAR CHART
        var barChartCanvas = $('#barChart').get(0).getContext('2d');
        var barChartData = {
            labels: [<?php echo $bar_chart_labels_js; ?>],
            datasets: [
                {
                    label: 'Sales Revenue',
                    backgroundColor: 'rgba(60,141,188,0.9)',
                    borderColor: 'rgba(60,141,188,0.8)',
                    pointRadius: false,
                    pointColor: '#3b8bba',
                    pointStrokeColor: 'rgba(60,141,188,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                    data: [<?php echo $bar_chart_data_js; ?>]
                }
            ]
        };
        var barChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            datasetFill: false,
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false,
                    }
                }],
                yAxes: [{
                    gridLines: {
                        display: false,
                    }
                }]
            }
        };
        new Chart(barChartCanvas, {
            type: 'bar',
            data: barChartData,
            options: barChartOptions
        });

        // PIE CHART
        var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
        var pieData = {
            labels: [<?php echo $pie_chart_labels_js; ?>],
            datasets: [
                {
                    data: [<?php echo $pie_chart_data_js; ?>],
                    backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc']
                }
            ]
        };
        var pieOptions = {
            responsive: true,
            maintainAspectRatio: false
        };
        new Chart(pieChartCanvas, {
            type: 'pie',
            data: pieData,
            options: pieOptions
        });
    });
</script>