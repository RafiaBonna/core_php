<?php
include_once __DIR__ . '/../../config.php';

// Fetch all customers for the dropdown
$customers = [];
$customer_sql = "SELECT id, name FROM customers ORDER BY name ASC";
$customer_result = $conn->query($customer_sql);
if ($customer_result && $customer_result->num_rows > 0) {
    while ($row = $customer_result->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Fetch all products for the search dropdown
$products = [];
$product_sql = "SELECT id, product_name, sale_price, quantity FROM stock WHERE quantity > 0 ORDER BY product_name ASC";
$product_result = $conn->query($product_sql);
if ($product_result && $product_result->num_rows > 0) {
    while ($row = $product_result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Handle form submission for a new sale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_sale'])) {
    $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
    $total_amount = $_POST['total_amount'];
    $payment_method = $_POST['payment_method'];
    $items = json_decode($_POST['items'], true); // Decode the JSON string

    $conn->begin_transaction();
    try {
        // Step 1: Insert into the sales table
        $stmt_sale = $conn->prepare("INSERT INTO sales (customer_id, total_amount, payment_method) VALUES (?, ?, ?)");
        if (!$stmt_sale) {
            throw new Exception("Error preparing statement for sales table: " . $conn->error);
        }
        $stmt_sale->bind_param("ids", $customer_id, $total_amount, $payment_method);
        if (!$stmt_sale->execute()) {
            throw new Exception("Error executing statement for sales table: " . $stmt_sale->error);
        }
        $sale_id = $conn->insert_id;
        $stmt_sale->close();

        // Step 2: Insert into the sale_items table and update stock
        $stmt_item = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        if (!$stmt_item) {
            throw new Exception("Error preparing statement for sale_items table: " . $conn->error);
        }

        $stmt_stock = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE id = ?");
        if (!$stmt_stock) {
            throw new Exception("Error preparing statement for stock table: " . $conn->error);
        }

        foreach ($items as $item) {
            $product_id = $item['id'];
            $quantity = $item['quantity'];
            $unit_price = $item['price'];

            $stmt_item->bind_param("iiid", $sale_id, $product_id, $quantity, $unit_price);
            if (!$stmt_item->execute()) {
                throw new Exception("Error executing statement for sale_items: " . $stmt_item->error);
            }

            $stmt_stock->bind_param("ii", $quantity, $product_id);
            if (!$stmt_stock->execute()) {
                throw new Exception("Error updating stock: " . $stmt_stock->error);
            }
        }
        $stmt_item->close();
        $stmt_stock->close();

        $conn->commit();
        $message = "<div class='alert alert-success'>Sale recorded successfully! Sale ID: " . $sale_id . "</div>";
        // Optionally redirect to invoice page
        header("Location: home.php?page=13&sale_id=" . $sale_id);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $message = "<div class='alert alert-danger'>Failed to process sale: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Create New Sale</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Create Sale</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">New Sales Transaction</h3>
        </div>
        <div class="card-body">
            <?php if (isset($message)) echo $message; ?>
            <form action="" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_id">Customer</label>
                            <select name="customer_id" id="customer_id" class="form-control select2">
                                <option value="">Guest Customer</option>
                                <?php foreach ($customers as $customer) : ?>
                                    <option value="<?php echo htmlspecialchars($customer['id']); ?>">
                                        <?php echo htmlspecialchars($customer['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="product_id">Add Products</label>
                            <select id="product_id" class="form-control select2">
                                <option value="">Select a product to add</option>
                                <?php foreach ($products as $product) : ?>
                                    <option
                                        value="<?php echo htmlspecialchars($product['id']); ?>"
                                        data-price="<?php echo htmlspecialchars($product['sale_price']); ?>"
                                        data-stock="<?php echo htmlspecialchars($product['quantity']); ?>"
                                    >
                                        <?php echo htmlspecialchars($product['product_name']); ?> (Stock: <?php echo htmlspecialchars($product['quantity']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <h4>Cart Items</h4>
                        <div class="card-body p-0">
                            <table class="table table-bordered table-sm" style="display: none;" id="cart-table">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th style="width: 100px;">Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="cart-items">
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-control" required>
                                <option value="Cash">Cash</option>
                                <option value="Card">Card</option>
                                <option value="Mobile Banking">Mobile Banking</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="total_amount">Total Amount</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" name="total_amount" id="total_amount" class="form-control" readonly required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="items" id="cart_items_json">
                
                <button type="submit" name="process_sale" class="btn btn-success btn-block" disabled>Process Sale</button>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var cart = [];

    // Initialize Select2 on the product and customer dropdowns
    $('#product_id, #customer_id').select2({
        placeholder: "Select an option",
        allowClear: true,
        width: '100%'
    });

    // Function to update the cart display and total amount
    function updateCartDisplay() {
        var cartTableBody = $('#cart-items');
        cartTableBody.empty(); // Clear existing rows
        var totalAmount = 0;

        if (cart.length > 0) {
            $('#cart-table').show(); // Show the table if there are items
            $('button[name="process_sale"]').prop('disabled', false);
        } else {
            $('#cart-table').hide(); // Hide the table if it's empty
            $('button[name="process_sale"]').prop('disabled', true);
        }

        cart.forEach(function(item) {
            var rowTotal = item.price * item.quantity;
            totalAmount += rowTotal;

            var rowHtml = `
                <tr>
                    <td>${item.name}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm item-quantity" data-id="${item.id}" value="${item.quantity}" min="1" max="${item.available_stock}" style="width: 80px;">
                    </td>
                    <td>৳${item.price.toFixed(2)}</td>
                    <td>৳${rowTotal.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-danger btn-sm remove-item" data-id="${item.id}">Remove</button>
                    </td>
                </tr>
            `;
            cartTableBody.append(rowHtml);
        });

        // Update the total amount display and the hidden input field
        $('#total_amount').val(totalAmount.toFixed(2));
        $('#total-amount-display').text(totalAmount.toFixed(2));

        // Update the hidden input for form submission
        $('#cart_items_json').val(JSON.stringify(cart));
    }

    // Handle product selection
    $('#product_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var productId = selectedOption.val();

        if (productId) {
            var productName = selectedOption.text().split(' (Stock:')[0].trim();
            var productPrice = parseFloat(selectedOption.data('price'));
            var availableStock = parseInt(selectedOption.data('stock'));

            if (availableStock <= 0) {
                alert('This product is out of stock.');
                return;
            }

            // Check if the item is already in the cart
            var existingItem = cart.find(item => item.id == productId);

            if (existingItem) {
                if (existingItem.quantity < availableStock) {
                    existingItem.quantity++;
                } else {
                    alert('Maximum stock reached for this product.');
                }
            } else {
                cart.push({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    quantity: 1,
                    available_stock: availableStock
                });
            }
            updateCartDisplay();
            // Reset the select box to prevent double-add
            $(this).val(null).trigger('change');
        }
    });

    // Handle removing an item from the cart
    $(document).on('click', '.remove-item', function() {
        var productId = $(this).data('id');
        cart = cart.filter(item => item.id != productId);
        updateCartDisplay();
    });

    // Handle quantity change
    $(document).on('change', '.item-quantity', function() {
        var productId = $(this).data('id');
        var newQuantity = parseInt($(this).val());
        var item = cart.find(item => item.id == productId);

        if (newQuantity > 0 && newQuantity <= item.available_stock) {
            item.quantity = newQuantity;
        } else {
            alert('Invalid quantity or exceeds available stock.');
            $(this).val(item.quantity); // Revert to old value
        }
        updateCartDisplay();
    });
});
</script>