<?php
// Start session once at the very beginning
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Define a base path constant for easier includes
define('BASE_PATH', __DIR__ . '/include/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="dist/plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="dist/plugins/daterangepicker/daterangepicker.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
<?php
    include(BASE_PATH . 'header.php');
    include(BASE_PATH . 'nav.php');
    include(BASE_PATH . 'sidebar.php');
?>

  <!-- Main content -->
  <div class="content-wrapper">

    <!-- Dashboard header -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard</h1>
          </div>
        </div>
      </div>
    </div>

    <!-- Dashboard main content -->
    <section class="content">
      <div class="container-fluid">

        <!-- Small boxes -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3>150</h3>
                <p>New Orders</p>
              </div>
              <div class="icon"><i class="ion ion-bag"></i></div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3>53<sup style="font-size: 20px">%</sup></h3>
                <p>Bounce Rate</p>
              </div>
              <div class="icon"><i class="ion ion-stats-bars"></i></div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>44</h3>
                <p>User Registrations</p>
              </div>
              <div class="icon"><i class="ion ion-person-add"></i></div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>65</h3>
                <p>Unique Visitors</p>
              </div>
              <div class="icon"><i class="ion ion-pie-graph"></i></div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        </div>

        <!-- Placeholder content -->
        <div class="card">
          <?php include("placholder.php"); ?>
        </div>

      </div>
    </section>

  </div>
  <!-- /.content-wrapper -->

  <?php include(BASE_PATH . 'footer.php'); ?>

</div>
<!-- ./wrapper -->

<!-- JS Scripts -->
<script src="dist/plugins/jquery/jquery.min.js"></script>
<script src="dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/dist/js/adminlte.min.js"></script>
<script src="dist/dist/js/demo.js"></script>
<script>
  $(function() {
    'use strict'
    $('.todo-list').TodoWidget()
  })
</script>

</body>
</html>
