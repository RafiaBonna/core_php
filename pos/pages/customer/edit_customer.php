<?php
include_once __DIR__ . '/../../config.php';

$message = '';
$customer_data = [];

// Fetch customer data for display
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $customer_data = $result->fetch_assoc();
    } else {
        $message = "Customer not found.";
    }
    $stmt->close();
}

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_customer'])) {
    $id = intval($_POST['id']);
    $customer_name = trim($_POST['customer_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if ($customer_name) {
        // Correct the column name from `customer_name` to `name`
        $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $customer_name, $email, $phone, $address, $id);
        
        if ($stmt->execute()) {
            $message = "Customer updated successfully!";
            // Redirect to prevent form resubmission
            // header("Location: home.php?page=10");
            // exit; // Stop further script execution
        } else {
            $message = "Error updating customer: " . $conn->error;
        }
        $stmt->close();
    } else {
        $message = "<div class='alert alert-danger'>Error: Customer name is required.</div>";
    }
}
?>

<div class="container-fluid">
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">Edit Customer</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($message)): ?>
                <div class="alert alert-info">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($customer_data)): ?>
                <form action="home.php?page=19&id=<?php echo htmlspecialchars($customer_data['id']); ?>" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($customer_data['id']); ?>">
                    <div class="form-group mb-3">
                        <label for="customer_name">Customer Name</label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control" value="<?php echo htmlspecialchars($customer_data['name']); ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($customer_data['email']); ?>">
                    </div>
                    <div class="form-group mb-3">
                        <label for="phone">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($customer_data['phone']); ?>">
                    </div>
                    <div class="form-group mb-3">
                        <label for="address">Address</label>
                        <textarea name="address" id="address" class="form-control" rows="3"><?php echo htmlspecialchars($customer_data['address']); ?></textarea>
                    </div>
                    <button type="submit" name="update_customer" class="btn btn-warning btn-block">Update Customer</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>