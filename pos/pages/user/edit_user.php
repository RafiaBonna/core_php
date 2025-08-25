<?php
include __DIR__ . '/../../config.php';

$fname = "";
$lname = "";
$email = "";
$role = "";
$id = "";
$message = "";

// Handle form submission for updating user
if (isset($_POST["btnUpdate"])) {
    $id = $_POST["id"];
    $fname = trim($_POST["fname"]);
    $lname = trim($_POST["uname"]);
    $email = trim($_POST["mail"]);
    $role = trim($_POST["role"]); // This should be role_id

    if ($fname && $lname && $email && $role && $id) {
        $stmt = $conn->prepare("UPDATE users SET full_name=?, username=?, email=?, role_id=? WHERE id=?");
        $stmt->bind_param("sssii", $fname, $lname, $email, $role, $id);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>User updated successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error updating record: " . $conn->error . "</div>";
        }
        $stmt->close();
    } else {
        $message = "<div class='alert alert-danger'>All fields are required.</div>";
    }
}

// Fetch user data for editing
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql_fetch = "SELECT full_name, username, email, role_id FROM users WHERE id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $id);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $fname = $row['full_name'];
            $lname = $row['username'];
            $email = $row['email'];
            $role = $row['role_id'];
        } else {
            $message = "<div class='alert alert-danger'>No user found with that ID.</div>";
        }
        $stmt_fetch->close();
    }
} else {
    $message = "<div class='alert alert-danger'>Invalid request. No user ID provided.</div>";
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Edit User</h3>
                </div>
                <?php echo $message; ?>
                <form action="home.php?page=3&id=<?php echo htmlspecialchars($id); ?>" method="post">
                    <div class="card-body">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                        <div class="form-group">
                            <label for="y">Full Name</label>
                            <input type="text" class="form-control" id="y" name="fname" value="<?php echo htmlspecialchars($fname); ?>">
                        </div>
                        <div class="form-group">
                            <label for="y">User Name</label>
                            <input type="text" class="form-control" id="y" name="uname" value="<?php echo htmlspecialchars($lname); ?>">
                        </div>
                        <div class="form-group">
                            <label for="p">Email</label>
                            <input type="text" class="form-control" id="p" name="mail" value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                        <div class="form-group">
                            <label for="r">Role Name</label>
                            <input type="text" class="form-control" id="r" name="role" value="<?php echo htmlspecialchars($role); ?>">
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" name="btnUpdate">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>