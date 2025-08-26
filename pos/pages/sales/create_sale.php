<?php
// Include the database connection file.
include_once __DIR__ . '/../../config.php';

// Initialize a message variable for user feedback.
$message = '';

// Fetch all customers for the dropdown, ordered by name.
$customers = [];
$customer_sql = "SELECT id, name FROM customers ORDER BY name ASC";
$customer_result = $conn->query($customer_sql);
if ($customer_result && $customer_result->num_rows > 0) {
    while ($row = $customer_result->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Fetch all products with available stock for the product dropdown.
$products = [];
$product_sql = "SELECT id, product_name, sale_price, quantity FROM stock WHERE quantity > 0 ORDER BY product_name ASC";
$product_result = $conn->query($product_sql);
if ($product_result && $product_result->num_rows > 0) {
    while ($row = $product_result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Handle form submission for a new sale.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_sale'])) {
    $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
    $total_amount = $_POST['total_amount'];
    $payment_method = $_POST['payment_method'];
    $items = json_decode($_POST['items'], true); // Decode the JSON string from the hidden input.

    if (empty($items)) {
        $message = "<div class='alert alert-danger'>Error: Cannot process a sale with no items.</div>";
    } else {
        // Begin a database transaction to ensure data integrity.
        $conn->begin_transaction();
        try {
            // Step 1: Insert into the sales table.
            $stmt_sale = $conn->prepare("INSERT INTO sales (customer_id, total_amount, payment_method) VALUES (?, ?, ?)");
            if (!$stmt_sale) {
                throw new Exception("Error preparing statement for sales table: " . $conn->error);
            }
            $stmt_sale->bind_param("ids", $customer_id, $total_amount, $payment_method);
            if (!$stmt_sale->execute()) {
                throw new Exception("Error executing statement for sales table: " . $stmt_sale->error);
            }
            $sale_id = $conn->insert_id; // Get the ID of the new sale.
            $stmt_sale->close();

            // Step 2: Loop through the items and insert into sales_items and update stock.
            $stmt_sale_item = $conn->prepare("INSERT INTO sales_items (sale_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $stmt_stock_update = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE id = ?");

            if (!$stmt_sale_item || !$stmt_stock_update) {
                throw new Exception("Error preparing statements for sale items or stock update.");
            }

            foreach ($items as $item) {
                // Insert the sold item into the sales_items table.
                $stmt_sale_item->bind_param("iiid", $sale_id, $item['id'], $item['quantity'], $item['price']);
                if (!$stmt_sale_item->execute()) {
                    throw new Exception("Error executing statement for sale items: " . $stmt_sale_item->error);
                }

                // Update the stock quantity.
                $stmt_stock_update->bind_param("ii", $item['quantity'], $item['id']);
                if (!$stmt_stock_update->execute()) {
                    throw new Exception("Error executing statement for stock update: " . $stmt_stock_update->error);
                }
            }

            $stmt_sale_item->close();
            $stmt_stock_update->close();

            // Commit the transaction if all queries are successful.
            $conn->commit();
            $message = "<div class='alert alert-success'>Sale processed successfully! Sale ID: " . $sale_id . "</div>";
        } catch (Exception $e) {
            // Roll back the transaction in case of any error.
            $conn->rollback();
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
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
            <h3 class="card-title">New Sale Transaction</h3>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            <form id="saleForm" method="POST" action="">
                <!-- Customer Selection -->
                <div class="form-group mb-3">
                    <label for="customer_id">Customer Name (Optional)</label>
                    <select name="customer_id" id="customer_id" class="form-control select2">
                        <option value="">Select a Customer</option>
                        <?php foreach($customers as $customer): ?>
                            <option value="<?php echo htmlspecialchars($customer['id']); ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Product Selection and Add to Cart -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="product_id">Add Product</label>
                        <select name="product_id" id="product_id" class="form-control select2">
                            <option value="">Select a Product</option>
                            <?php foreach($products as $product): ?>
                                <option value="<?php echo htmlspecialchars($product['id']); ?>" 
                                        data-price="<?php echo htmlspecialchars($product['sale_price']); ?>"
                                        data-stock="<?php echo htmlspecialchars($product['quantity']); ?>">
                                    <?php echo htmlspecialchars($product['product_name']); ?> - (Stock: <?php echo htmlspecialchars($product['quantity']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" id="addToCart" class="btn btn-info btn-block">Add to Cart</button>
                    </div>
                </div>

                <!-- Cart Display Table -->
                <h4 class="mt-4">Cart Items</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="cartTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Cart items will be added here by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Final Sale Details -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-control" required>
                                <option value="Cash">Cash</option>
                                <option value="Card">Card</option>
                                <option value="Mobile Payment">Mobile Payment</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="total_amount">Total Amount</label>
                            <input type="text" name="total_amount" id="total_amount" class="form-control" readonly value="0.00">
                        </div>
                    </div>
                </div>

                <!-- Hidden input to store cart items as JSON -->
                <input type="hidden" name="items" id="cartItemsInput">

                <button type="submit" name="process_sale" id="processSaleBtn" class="btn btn-success btn-block" disabled>Process Sale</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal message box structure -->
<div id="messageModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div class="modal-content" style="background-color:#fff; padding:20px; border-radius:8px; text-align:center; max-width:400px; box-shadow:0 4px 8px rgba(0,0,0,0.1);">
        <h5 id="modalTitle"></h5>
        <p id="modalMessage"></p>
        <div class="modal-footer" style="margin-top:20px;">
            <button id="closeModalBtn" class="btn btn-secondary">OK</button>
        </div>
    </div>
</div>

<!-- Ensure jQuery is loaded first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Then load Select2 and other scripts -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>

<!-- <script>
$(document).ready(function() {
    // Custom function to show the modal message box
    function showMessage(title, message) {
        $('#modalTitle').text(title);
        $('#modalMessage').text(message);
        $('#messageModal').css('display', 'flex');
    }

    // Custom function to hide the modal message box
    $('#closeModalBtn').on('click', function() {
        $('#messageModal').css('display', 'none');
    });

    // Initialize Select2 for search functionality on dropdowns.
    $('.select2').select2({
        placeholder: "Start typing to search...",
        allowClear: true
    });

    // An empty array to hold our cart items.
    let cart = [];

    // Add to Cart functionality.
    $('#addToCart').on('click', function() {
        var productId = $('#product_id').val();
        var quantity = parseInt($('#quantity').val());

        // Client-side validation.
        if (!productId || quantity <= 0) {
            showMessage('Validation Error', 'Please select a product and enter a valid quantity.');
            return;
        }

        var product = $('#product_id option:selected');
        var productName = product.text().split('-')[0].trim();
        var productPrice = parseFloat(product.data('price'));
        var availableStock = parseInt(product.data('stock'));

        if (quantity > availableStock) {
            showMessage('Stock Error', 'The quantity exceeds the available stock.');
            return;
        }

        // Check if the item is already in the cart.
        let existingItem = cart.find(item => item.id == productId);
        if (existingItem) {
            // Update quantity if item exists.
            let newQuantity = existingItem.quantity + quantity;
            if (newQuantity > availableStock) {
                showMessage('Stock Error', 'Adding this quantity exceeds available stock.');
                return;
            }
            existingItem.quantity = newQuantity;
        } else {
            // Add new item to the cart.
            cart.push({
                id: productId,
                name: productName,
                price: productPrice,
                quantity: quantity
            });
        }
        
        updateCartDisplay();
        // Reset the product and quantity fields.
        $('#product_id').val(null).trigger('change');
        $('#quantity').val(1);
    });

    // Handle removing an item from the cart.
    $(document).on('click', '.remove-item', function() {
        var productId = $(this).data('id');
        cart = cart.filter(item => item.id != productId);
        updateCartDisplay();
    });

    // Handle quantity change directly in the cart table.
    $(document).on('change', '.item-quantity', function() {
        var productId = $(this).data('id');
        var newQuantity = parseInt($(this).val());
        var item = cart.find(item => item.id == productId);
        
        var productOption = $(`#product_id option[value='${productId}']`);
        var availableStock = parseInt(productOption.data('stock'));

        if (newQuantity > 0 && newQuantity <= availableStock) {
            item.quantity = newQuantity;
        } else {
            showMessage('Invalid Quantity', 'Invalid quantity or exceeds available stock.');
            $(this).val(item.quantity); // Revert to old value.
        }
        updateCartDisplay();
    });

    // This function updates the cart table and total amount.
    function updateCartDisplay() {
        let totalAmount = 0;
        let cartTableBody = $('#cartTable tbody');
        cartTableBody.empty(); // Clear the table body.

        if (cart.length > 0) {
            $('#processSaleBtn').prop('disabled', false);
        } else {
            $('#processSaleBtn').prop('disabled', true);
        }
        
        cart.forEach(item => {
            let itemTotal = item.price * item.quantity;
            totalAmount += itemTotal;
            let row = `
                <tr>
                    <td>${item.name}</td>
                    <td>$${item.price.toFixed(2)}</td>
                    <td><input type="number" class="form-control item-quantity" data-id="${item.id}" value="${item.quantity}" min="1"></td>
                    <td>$${itemTotal.toFixed(2)}</td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-item" data-id="${item.id}">Remove</button></td>
                </tr>
            `;
            cartTableBody.append(row);
        });

        $('#total_amount').val(totalAmount.toFixed(2));
        $('#cartItemsInput').val(JSON.stringify(cart)); // Update hidden input with JSON.
    }
});
</script> -->
