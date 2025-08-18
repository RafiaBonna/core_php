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
                // Password is correct, start a session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['role_name'] = $user['role_name'];

                // Redirect to the dashboard
                header('Location: home.php');
                exit;
            } else {
                // Incorrect password
                $error = 'Invalid email or password.';
            }
        } else {
            // User with that email does not exist
            $error = 'Invalid email or password.';
        }

        $stmt->close();
    }
}
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AdminLTE 3 | Log in</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="dist/dist/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="dist/dist/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="dist/dist/css/adminlte.min.css">
    <style>
        .login-box {
            width: 400px !important;
        }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="#" class="h1"><b>POS</b>System</a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Sign in to start your session</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
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
                            <label for="remember">Remember Me</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                    </div>
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
            <p class="mb-1">
                <a href="#">I forgot my password</a>
            </p>
            <p class="mb-0">
                <a href="register.php" class="text-center">Register a new membership</a>
            </p>
        </div>
        </div>
    </div>
<script src="dist/dist/plugins/jquery/jquery.min.js"></script>
<script src="dist/dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/dist/js/adminlte.min.js"></script>
</body>
</html>