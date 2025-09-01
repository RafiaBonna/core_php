<?php
// Include the database connection file
include_once __DIR__ . '/../../config.php';

$message = "";

// Handle adding a new customer.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    if (empty($customer_name)) {
        $message = "<div class='alert alert-danger'>Error: Customer name is required.</div>";
    } else {
        // Check if the customer name already exists.
        $sql_check = "SELECT id FROM customers WHERE name = ?";
        $stmt_check = $conn->prepare($sql_check);
        if ($stmt_check) {
            $stmt_check->bind_param("s", $customer_name);
            $stmt_check->execute();
            $stmt_check->store_result();
            
            if ($stmt_check->num_rows > 0) {
                $message = "<div class='alert alert-danger'>Error: Customer with this name already exists.</div>";
            } else {
                // Correct SQL query for inserting into the 'customers' table
                $sql_insert = "INSERT INTO customers (name, email, phone, address, created_at) VALUES (?, ?, ?, ?, NOW())";
                $stmt_insert = $conn->prepare($sql_insert);
                
                if ($stmt_insert) {
                    $stmt_insert->bind_param("ssss", $customer_name, $email, $phone, $address);
                    
                    if ($stmt_insert->execute()) {
                        // Redirect with a success message after adding the customer
                        // header("Location: home.php?page=10&status=success&message=" . urlencode("Customer added successfully!"));
                        // exit();
                    } else {
                        $message = "<div class='alert alert-danger'>Error adding customer: " . $stmt_insert->error . "</div>";
                    }
                    $stmt_insert->close();
                } else {
                    $message = "<div class='alert alert-danger'>SQL prepare failed: " . $conn->error . "</div>";
                }
            }
            $stmt_check->close();
        }
    }
}

// Handle customer deletion
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $customer_id = intval($_GET['delete_id']);

    // Prepare and execute the delete query
    $sql_delete = "DELETE FROM customers WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $customer_id);
        if ($stmt_delete->execute()) {
            $message = "<div class='alert alert-success'>Customer deleted successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error deleting customer: " . $stmt_delete->error . "</div>";
        }
        $stmt_delete->close();
    } else {
        $message = "<div class='alert alert-danger'>SQL prepare failed: " . $conn->error . "</div>";
    }
    // Redirect to the correct page to prevent re-submission on refresh
    // header("Location: home.php?page=22&message=" . urlencode(strip_tags($message)));
    // exit();
}

// Check for messages in the URL
if (isset($_GET['status']) && $_GET['status'] === 'success' && isset($_GET['message'])) {
    $message = "<div class='alert alert-success'>" . htmlspecialchars($_GET['message']) . "</div>";
} else if (isset($_GET['status']) && $_GET['status'] === 'error' && isset($_GET['message'])) {
    $message = "<div class='alert alert-danger'>" . htmlspecialchars($_GET['message']) . "</div>";
} else if (isset($_GET['message'])) {
    $message = "<div class='alert alert-info'>" . htmlspecialchars($_GET['message']) . "</div>";
}

// Fetch all customers from the database to display
$customers = [];
$sql_select = "SELECT id, name, email, phone FROM customers ORDER BY created_at DESC";
$result = $conn->query($sql_select);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Manage Customers</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Customers</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Add New Customer</h3>
        </div>
        <form method="post" action="home.php?page=22">
            <div class="card-body">
                <?php echo $message; ?>
                <div class="form-group mb-3">
                    <label for="customer_name">Customer Name</label>
                    <input type="text" name="customer_name" id="customer_name" class="form-control" placeholder="Enter customer name" required>
                </div>
                <div class="form-group mb-3">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter customer email">
                </div>
                <div class="form-group mb-3">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter customer phone">
                </div>
                <div class="form-group mb-3">
                    <label for="address">Address</label>
                    <textarea name="address" id="address" class="form-control" rows="3" placeholder="Enter customer address"></textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="add_customer" class="btn btn-primary">Add Customer</button>
            </div>
        </form>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Customers</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="customerTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($customers)): ?>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td class="text-center"><?= htmlspecialchars($customer['id']); ?></td>
                                    <td><?= htmlspecialchars($customer['name']); ?></td>
                                    <td><?= htmlspecialchars($customer['email']); ?></td>
                                    <td><?= htmlspecialchars($customer['phone']); ?></td>
                                    <td class="d-flex justify-content-center gap-2">
                                       <a href="home.php?page=23&id=<?= $customer['id']; ?>" class="btn btn-sm btn-primary">Edit</a> &nbsp;
                                       <a href="home.php?page=22&delete_id=<?= $customer['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this customer?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No customers found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
