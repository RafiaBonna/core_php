<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Static Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .main-container {
            padding: 20px;
        }
        .card-with-icon {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-with-icon .icon-large {
            font-size: 3rem;
            color: #ced4da;
        }
        .dashboard-header {
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .sales-graph-card, .popular-items-card, .sales-stats-card {
            min-height: 400px;
        }
    </style>
</head>
<body>

<div class="main-container">

    <div class="row mb-4">
        <div class="col-12">
            <h2 class="dashboard-header">Dashboard <small class="text-muted fs-6"> &nbsp; > overview & stats</small></h2>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <h4>Today's Takings</h4>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-white h-100">
                <div class="card-body card-with-icon">
                    <div>
                        <p class="mb-0 text-muted">Sales</p>
                        <h4 class="mb-0">Rp0.00</h4>
                    </div>
                    <div class="icon-large text-success"><i class="fas fa-arrow-up"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-white h-100">
                <div class="card-body card-with-icon">
                    <div>
                        <p class="mb-0 text-muted">Refunds</p>
                        <h4 class="mb-0">Rp0.00</h4>
                    </div>
                    <div class="icon-large text-danger"><i class="fas fa-arrow-down"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-white h-100">
                <div class="card-body card-with-icon">
                    <div>
                        <p class="mb-0 text-muted">Cost</p>
                        <h4 class="mb-0">Rp0.00</h4>
                    </div>
                    <div class="icon-large text-warning"><i class="fas fa-box"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-white h-100">
                <div class="card-body card-with-icon">
                    <div>
                        <p class="mb-0 text-muted">Profit</p>
                        <h4 class="mb-0">Rp0.00</h4>
                    </div>
                    <div class="icon-large text-success"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card bg-white sales-graph-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Sales Graph</h5>
                    <span class="badge bg-primary">This Week</span>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-5">
                        <p>Graph placeholder. Integration with a charting library like Chart.js or Plotly would go here.</p>
                        <i class="fas fa-chart-area fa-5x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card bg-white h-100 popular-items-card">
                <div class="card-header">
                    <h5>Popular Items This Month</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Apple</span>
                            <span>Qty Sold: 71</span>
                            <span>Total: Rp70.29</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Banana</span>
                            <span>Qty Sold: 75</span>
                            <span>Total: Rp225.00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Coconut</span>
                            <span>Qty Sold: 116</span>
                            <span>Total: Rp348.00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Doritos</span>
                            <span>Qty Sold: 90</span>
                            <span>Total: Rp225.00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Energy Drink</span>
                            <span>Qty Sold: 76</span>
                            <span>Total: Rp362.20</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Chocolate Fudge</span>
                            <span>Qty Sold: 88</span>
                            <span>Total: Rp136.40</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card bg-white h-100 sales-stats-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Sale Stats</h5>
                    <span class="badge bg-primary">This Week</span>
                </div>
                <div class="card-body text-center">
                    <div class="d-flex justify-content-center align-items-center">
                        <p>Pie chart placeholder.</p>
                    </div>
                                        <ul class="list-inline mt-3">
                        <li class="list-inline-item"><i class="fas fa-circle text-primary"></i> Debit</li>
                        <li class="list-inline-item"><i class="fas fa-circle text-success"></i> Credit</li>
                        <li class="list-inline-item"><i class="fas fa-circle text-warning"></i> E-wallet</li>
                        <li class="list-inline-item"><i class="fas fa-circle text-danger"></i> Bank</li>
                    </ul>
                    <div class="row text-center mt-4">
                        <div class="col-4">
                            <h4 class="mb-0">181</h4>
                            <small class="text-muted">sales</small>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-0">0</h4>
                            <small class="text-muted">refunds</small>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-0">Rp53,393.60</h4>
                            <small class="text-muted">total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>