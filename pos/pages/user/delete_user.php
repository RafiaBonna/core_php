<?php
include __DIR__ . '/../../config.php';

if (!isset($_GET['id'])) {
    die("Invalid request!");
}

$delete_id = intval($_GET['id']);

// Prevent a user from deleting their own account
if (isset($_SESSION['user_id']) && $delete_id == $_SESSION['user_id']) {
    // Redirect back to manage_user.php with an error message
    header("Location: ../../home.php?page=2&error=You cannot delete your own account.");
    exit;
}

// Prepare and execute the DELETE statement
$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
if ($stmt) {
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        // Redirect back to manage_user.php with a success message
        header("Location: ../../home.php?page=2&success=User deleted successfully.");
    } else {
        // Redirect with an error message
        header("Location: ../../home.php?page=2&error=Error deleting user.");
    }
    $stmt->close();
} else {
    // Redirect with a database error message
    header("Location: ../../home.php?page=2&error=Database error.");
}
exit;
?>