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
                $sql_insert = "INSERT INTO customers (name, email, phone, address, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
                $stmt_insert = $conn->prepare($sql_insert);
                
                if ($stmt_insert) {
                    $stmt_insert->bind_param("ssss", $customer_name, $email, $phone, $address);
                    
                    if ($stmt_insert->execute()) {
                        $message = "<div class='alert alert-success'>Customer added successfully!</div>";
                    } else {
                        $message = "<div class='alert alert-danger'>Error: " . $stmt_insert->error . "</div>";
                    }
                    $stmt_insert->close();
                } else {
                    $message = "<div class='alert alert-danger'>Error preparing statement: " . $conn->error . "</div>";
                }
            }
            $stmt_check->close();
        } else {
            $message = "<div class='alert alert-danger'>Error preparing statement: " . $conn->error . "</div>";
        }
    }
}

// Handle deleting a customer
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $sql_delete = "DELETE FROM customers WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $id);
        if ($stmt_delete->execute()) {
            $message = "<div class='alert alert-success'>Customer deleted successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error deleting customer: " . $stmt_delete->error . "</div>";
        }
        $stmt_delete->close();
    }
}

// Fetch all customers from the database
$sql = "SELECT id, name, email, phone, address, created_at FROM customers ORDER BY created_at DESC";
$result = $conn->query($sql);
$customers = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}
?>


   <div class="content-fluid" style="min-height: 1000.6px;">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <h1 class="m-0">Manage Customers</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Manage Customers</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Add Customer Form - Full Width on Top -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Add New Customer</h3>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        <form action="home.php?page=10" method="post">
                            <div class="form-group mb-3">
                                <label for="customer_name">Customer Name</label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="email">Email (Optional)</label>
                                <input type="email" name="email" id="email" class="form-control">
                            </div>
                            <div class="form-group mb-3">
                                <label for="phone">Phone (Optional)</label>
                                <input type="text" name="phone" id="phone" class="form-control">
                            </div>
                            <div class="form-group mb-3">
                                <label for="address">Address (Optional)</label>
                                <textarea name="address" id="address" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" name="add_customer" class="btn btn-success btn-block">Add Customer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Table - Full Width Below Form -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Customers</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">Customer Name</th>
                                    <th class="text-center">Email</th>
                                    <th class="text-center">Phone</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($customers) > 0): ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td class="text-center"><?= htmlspecialchars($customer['id']); ?></td>
                                            <td><?= htmlspecialchars($customer['name']); ?></td>
                                            <td><?= htmlspecialchars($customer['email']); ?></td>
                                            <td><?= htmlspecialchars($customer['phone']); ?></td>
                                            <td class="d-flex justify-content-center">
                                                <a href="home.php?page=19&id=<?= $customer['id']; ?>" class="btn btn-sm btn-primary me-2">Edit</a>
                                                <a href="home.php?page=10&delete_id=<?= $customer['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this customer?');">Delete</a>
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
    </div>
</div>
