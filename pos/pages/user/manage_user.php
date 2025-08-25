<?php
include __DIR__ . '/../../config.php'; // Correct path

$message = "";

// Check for success or error messages from delete_user.php
if (isset($_GET['success'])) {
    $message = "<div class='alert alert-success'>" . htmlspecialchars($_GET['success']) . "</div>";
} elseif (isset($_GET['error'])) {
    $message = "<div class='alert alert-danger'>" . htmlspecialchars($_GET['error']) . "</div>";
}

$sql = "SELECT u.id, u.full_name, u.username, u.email, r.role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id DESC";
$result = $conn->query($sql);
?>

<div class="container my-5">
    <h3>Manage Users</h3>
    <a href="home.php?page=1" class="btn btn-success mb-3">Add New User</a>
    <?php echo $message; ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['role_name']); ?></td>
                    <td>
                        <a href="home.php?page=3&id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="pages/user/delete_user.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>