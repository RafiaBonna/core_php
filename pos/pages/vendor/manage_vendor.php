<?php
// Include the database connection file
include_once __DIR__ . '/../../config.php';

$message = "";

// Handle adding a new vendor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vendor'])) {
    $vendor_name = mysqli_real_escape_string($conn, $_POST['vendor_name']);

    // Check if the vendor name already exists
    $sql_check = "SELECT id FROM vendors WHERE name = ?";
    $stmt_check = $conn->prepare($sql_check);
    if ($stmt_check) {
        $stmt_check->bind_param("s", $vendor_name);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $message = "<div class='alert alert-danger'>Error: Vendor with this name already exists.</div>";
        } else {
            // Correct SQL query for inserting into the 'vendors' table
            $sql_insert = "INSERT INTO vendors (name, created_at) VALUES (?, NOW())";
            $stmt_insert = $conn->prepare($sql_insert);
            
            if ($stmt_insert) {
                // bind_param for 's' (string) type
                $stmt_insert->bind_param("s", $vendor_name);
                
                if ($stmt_insert->execute()) {
                    $message = "<div class='alert alert-success'>Vendor added successfully!</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Error adding vendor: " . $stmt_insert->error . "</div>";
                }
                $stmt_insert->close();
            } else {
                $message = "<div class='alert alert-danger'>Database error: " . $conn->error . "</div>";
            }
        }
        $stmt_check->close();
    } else {
        $message = "<div class='alert alert-danger'>Database error: " . $conn->error . "</div>";
    }
}

// Handle deleting a vendor
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);

    $sql_delete = "DELETE FROM vendors WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $delete_id);
        if ($stmt_delete->execute()) {
            $message = "<div class='alert alert-success'>Vendor deleted successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error deleting vendor: " . $stmt_delete->error . "</div>";
        }
        $stmt_delete->close();
    } else {
        $message = "<div class='alert alert-danger'>Database error: " . $conn->error . "</div>";
    }
}

// Fetch all vendors from the database to display in the table
$sql_vendors = "SELECT id, name, created_at FROM vendors ORDER BY created_at DESC";
$result_vendors = $conn->query($sql_vendors);
$vendors = [];
if ($result_vendors->num_rows > 0) {
    while ($row = $result_vendors->fetch_assoc()) {
        $vendors[] = $row;
    }
}
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header bg-primary">
                    <h4 class="card-title text-white">Add New Vendor</h4>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    <form action="" method="POST">
                        <div class="form-group mb-3">
                            <label for="vendor_name">Vendor Name</label>
                            <input type="text" name="vendor_name" id="vendor_name" class="form-control" required>
                        </div>
                        <button type="submit" name="add_vendor" class="btn btn-primary btn-block">Add Vendor</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-info mt-5">
        <div class="card-header bg-info">
            <h4 class="card-title text-white ">Manage Vendors</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-sm">
                    <thead class="thead-dark">
                        <tr class="text-center">
                            <th>ID</th>
                            <th>Vendor Name</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($vendors) > 0): ?>
                            <?php foreach ($vendors as $vendor): ?>
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($vendor['id']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($vendor['name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($vendor['created_at']); ?></td>
                                    <td class="d-flex justify-content-center">
                                        <a href="home.php?page=8&delete_id=<?php echo $vendor['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this vendor?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No vendors found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>