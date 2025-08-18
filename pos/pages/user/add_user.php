<?php
include __DIR__ . '/../../config.php'; // Correct path

$error = '';
$success = '';
$full_name = $username = $email = $role_id = '';

$roles = [];
$roles_sql = "SELECT id, role_name FROM roles";
$roles_result = $conn->query($roles_sql);
if ($roles_result && $roles_result->num_rows > 0) {
    while ($row = $roles_result->fetch_assoc()) $roles[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $role_id   = $_POST['role_id'];

    if ($full_name && $username && $email && $password && $role_id) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, full_name, email, role_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssssi", $username, $hashed_password, $full_name, $email, $role_id);
        if ($stmt->execute()) $success = "New user added successfully!";
        else $error = $stmt->error;
        $stmt->close();
    } else $error = "All fields are required.";
}
?>

<!-- HTML Form (Bootstrap) -->
<div class="container my-5">
    <h3>Add User</h3>
    <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="post">
        <input type="text" name="full_name" placeholder="Full Name" class="form-control mb-2" value="<?php echo htmlspecialchars($full_name); ?>" required>
        <input type="text" name="username" placeholder="Username" class="form-control mb-2" value="<?php echo htmlspecialchars($username); ?>" required>
        <input type="email" name="email" placeholder="Email" class="form-control mb-2" value="<?php echo htmlspecialchars($email); ?>" required>
        <input type="password" name="password" placeholder="Password" class="form-control mb-2" required>
        <select name="role_id" class="form-select mb-2" required>
            <option value="">Select Role</option>
            <?php foreach($roles as $role): ?>
            <option value="<?php echo $role['id']; ?>" <?php if($role_id==$role['id']) echo "selected"; ?>>
                <?php echo htmlspecialchars($role['role_name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary w-100">Add User</button>
    </form>
</div>
