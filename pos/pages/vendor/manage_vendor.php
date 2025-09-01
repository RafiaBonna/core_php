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
                $message = "<div class='alert alert-danger'>SQL prepare failed: " . $conn->error . "</div>";
            }
        }
        $stmt_check->close();
    }
}

// Handle vendor deletion
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $vendor_id = intval($_GET['delete_id']);

    // Prepare and execute the delete query
    $sql_delete = "DELETE FROM vendors WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $vendor_id);
        if ($stmt_delete->execute()) {
            $message = "<div class='alert alert-success'>Vendor deleted successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error deleting vendor: " . $stmt_delete->error . "</div>";
        }
        $stmt_delete->close();
    } else {
        $message = "<div class='alert alert-danger'>SQL prepare failed: " . $conn->error . "</div>";
    }
    // Redirect to the same page to prevent re-submission on refresh
    // header("Location: home.php?page=8&message=" . urlencode(strip_tags($message)));
    // exit();
}

// Fetch all vendors from the database to display
$vendors = [];
$sql_select = "SELECT id, name, created_at FROM vendors ORDER BY created_at DESC";
$result = $conn->query($sql_select);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $vendors[] = $row;
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Manage Vendors</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Vendors</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Add New Vendor</h3>
        </div>
       <form method="post" action="home.php?page=20">
            <div class="card-body">
                <?php echo $message; ?>
                <div class="form-group">
                    <label for="vendor_name">Vendor Name</label>
                    <input type="text" name="vendor_name" id="vendor_name" class="form-control" placeholder="Enter vendor name" required>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="add_vendor" class="btn btn-primary">Add Vendor</button>
            </div>
        </form>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Vendors</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="vendorTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center">Vendor Name</th>
                            <th class="text-center">Created At</th>
                            <th class="text-center">Actions</th>
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
                                       <a href="home.php?page=20&delete_id=<?php echo $vendor['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this vendor?');">Delete</a>
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
