<?php
include __DIR__ . '/../../config.php';

$message = "";
$user = null;
$roles = [];
$id = $_GET['id'] ?? null;

// Fetch roles for the dropdown menu
$sql_roles = "SELECT id, role_name FROM roles ORDER BY role_name ASC";
$result_roles = $conn->query($sql_roles);
if ($result_roles && $result_roles->num_rows > 0) {
    while ($row = $result_roles->fetch_assoc()) {
        $roles[] = $row;
    }
}

if ($id) {
    // Fetch the user data from the database
    $sql_user = "SELECT * FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    if ($stmt_user) {
        $stmt_user->bind_param("i", $id);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        if ($result_user->num_rows > 0) {
            $user = $result_user->fetch_assoc();
        } else {
            $message = "<div class='alert alert-danger'>User not found.</div>";
        }
        $stmt_user->close();
    }
} else {
    $message = "<div class='alert alert-danger'>Invalid user ID.</div>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnUpdate'])) {
    $id = $_POST['id'];
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role_id = $_POST['role_id'];
    $password = $_POST['password'] ?? '';

    // Start with the basic update query
    $sql_update = "UPDATE users SET full_name = ?, username = ?, email = ?, role_id = ?, updated_at = NOW() WHERE id = ?";
    $params = [$full_name, $username, $email, $role_id, $id];
    $param_types = "ssssi";

    // Check if a new password was provided
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql_update = "UPDATE users SET full_name = ?, username = ?, email = ?, password_hash = ?, role_id = ?, updated_at = NOW() WHERE id = ?";
        $params = [$full_name, $username, $email, $hashed_password, $role_id, $id];
        $param_types = "sssssi";
    }

    $stmt_update = $conn->prepare($sql_update);
    if ($stmt_update) {
        $stmt_update->bind_param($param_types, ...$params);
        if ($stmt_update->execute()) {
            $message = "<div class='alert alert-success'>User updated successfully!</div>";
            // Re-fetch user data to display the updated information
            header("Location: home.php?page=2");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Error updating user: " . $stmt_update->error . "</div>";
        }
        $stmt_update->close();
    } else {
        $message = "<div class='alert alert-danger'>Database error: " . $conn->error . "</div>";
    }
}

?>

<div class="container my-5">
    <h3>Update User</h3>
    <?php echo $message; ?>
    <?php if ($user): ?>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
        <div class="form-group mb-3">
            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" id="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="password">New Password (leave blank if not changing)</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>
        <div class="form-group mb-3">
            <label for="role_id">Role</label>
            <select name="role_id" id="role_id" class="form-control" required>
                <option value="">Select Role</option>
                <?php foreach ($roles as $role): ?>
                <option value="<?php echo htmlspecialchars($role['id']); ?>" <?php echo ($user['role_id'] == $role['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($role['role_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="btnUpdate" class="btn btn-primary">Update User</button>
    </form>
    <?php endif; ?>
</div>