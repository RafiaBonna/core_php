<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}

// Include database connection file
include_once 'config.php'; // Make sure this path is correct

$error = '';
$email = ''; // Keep email in the form on error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check if email and password are empty
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Prepare a SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT u.id, u.full_name, u.email, u.password_hash, u.role_id, r.role_name 
                                FROM users u
                                JOIN roles r ON u.role_id = r.id
                                WHERE u.email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify the hashed password
            if (password_verify($password, $user['password_hash'])) {
                // Password is correct, store user data in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role_name'];

                // Redirect to home page
                header('Location: home.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DREAM | POS</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="dist/plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="dist/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/dist/css/adminlte.min.css">

  <!-- Custom CSS for border, border-radius, and text color -->
  <style>
    .card {
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 10px;
    }
    .form-control {
      /* Set the text color to black and use !important to override AdminLTE dark mode style */
      color: #000 !important;
      border: 1px solid rgba(255, 255, 255, 0.4);
      border-radius: 5px;
    }
    /* This rule specifically targets the autofilled text color for WebKit browsers */
    .form-control:-webkit-autofill,
    .form-control:-webkit-autofill:hover,
    .form-control:-webkit-autofill:focus,
    .form-control:-webkit-autofill:active {
      -webkit-text-fill-color: #000 !important;
      transition: background-color 5000s ease-in-out 0s;
    }
    .input-group-text {
      border: 1px solid rgba(255, 255, 255, 0.4);
      border-left: none; /* Remove left border to match input field */
      border-radius: 0 5px 5px 0;
    }
    .btn {
      border-radius: 5px;
    }
  </style>

</head>
<body class="hold-transition login-page dark-mode">
<div class="login-box">
  <!-- /.login-logo -->
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="dist/index2.html" class="h1"><b>DREAM</b>POS</a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Sign in to start your session</p>
      
      <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>

      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="input-group mb-3">
          <input type="email" class="form-control" placeholder="Email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Password" name="password" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="remember">
              <label for="remember">
                Remember Me
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <div class="social-auth-links text-center mt-2 mb-3">
        <a href="#" class="btn btn-block btn-primary">
          <i class="fab fa-facebook mr-2"></i> Sign in using Facebook
        </a>
        <a href="#" class="btn btn-block btn-danger">
          <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
        </a>
      </div>
      <!-- /.social-auth-links -->

      <p class="mb-1">
        <a href="forgot-password.html">I forgot my password</a>
      </p>
      <p class="mb-0">
        <a href="register.html" class="text-center">Register a new membership</a>
      </p>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="dist/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/dist/js/adminlte.min.js"></script>
</body>
</html>
