<?php
// Include database connection
include_once __DIR__ . '/../../config.php';

// Fetch products from stock with available quantity
$medicinesData = [];
$medicines = $conn->query("SELECT id AS stock_id, product_name, sale_price, quantity FROM stock WHERE quantity > 0 ORDER BY product_name ASC");
if ($medicines) {
    while ($row = $medicines->fetch_assoc()) {
        $medicinesData[] = $row;
    }
}

// Fetch customers
$customersData = [];
$customers = $conn->query("SELECT id, name FROM customers ORDER BY name ASC");
if ($customers) {
    while ($row = $customers->fetch_assoc()) {
        $customersData[] = $row;
    }
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $total_amount = floatval($_POST['grand_total'] ?? 0);
    $stock_ids = $_POST['stock_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $unit_prices = $_POST['unit_price'] ?? [];
    $product_names = $_POST['product_name'] ?? [];

    // Begin a database transaction to ensure all operations succeed or fail together
    $conn->begin_transaction();
    try {
        // Handle customer
        $customer_id = null;
        if (!empty($customer_name)) {
            // Check if customer exists
            $stmt_check_customer = $conn->prepare("SELECT id FROM customers WHERE name = ?");
            if (!$stmt_check_customer) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_check_customer->bind_param("s", $customer_name);
            $stmt_check_customer->execute();
            $result_check_customer = $stmt_check_customer->get_result();

            if ($result_check_customer->num_rows > 0) {
                $customer_id = $result_check_customer->fetch_assoc()['id'];
            } else {
                // If customer doesn't exist, insert a new one
                $stmt_insert_customer = $conn->prepare("INSERT INTO customers (name) VALUES (?)");
                if (!$stmt_insert_customer) throw new Exception("Prepare failed: " . $conn->error);
                $stmt_insert_customer->bind_param("s", $customer_name);
                $stmt_insert_customer->execute();
                $customer_id = $conn->insert_id;
                $stmt_insert_customer->close();
            }
            $stmt_check_customer->close();
        }

        // Insert sale into 'sales' table
        $stmt_sale = $conn->prepare("INSERT INTO sales (customer_id, total_amount, sale_date) VALUES (?, ?, NOW())");
        if (!$stmt_sale) throw new Exception("Prepare failed: " . $conn->error);
        $stmt_sale->bind_param("id", $customer_id, $total_amount);
        $stmt_sale->execute();
        $sale_id = $conn->insert_id;
        $stmt_sale->close();

        // Insert each item into 'sale_items' table and update stock
        $stmt_item = $conn->prepare("INSERT INTO sale_items (sale_id, stock_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt_item) throw new Exception("Prepare failed: " . $conn->error);

        $stmt_stock = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE id = ?");
        if (!$stmt_stock) throw new Exception("Prepare failed: " . $conn->error);

        for ($i = 0; $i < count($stock_ids); $i++) {
            $stock_id = intval($stock_ids[$i]);
            $quantity = intval($quantities[$i]);
            $unit_price = floatval($unit_prices[$i]);
            $total_price = $quantity * $unit_price;

            // Insert into sale_items
            $stmt_item->bind_param("iiidd", $sale_id, $stock_id, $quantity, $unit_price, $total_price);
            $stmt_item->execute();

            // Update stock
            $stmt_stock->bind_param("ii", $quantity, $stock_id);
            $stmt_stock->execute();
        }
        $stmt_item->close();
        $stmt_stock->close();

        // Commit the transaction
        $conn->commit();
        $message = "<div class='alert alert-success'>Sale successfully completed!</div>";
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Create Sale</h1>
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
    <?php echo $message; ?>
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">New Sales Transaction</h3>
        </div>
        <form action="" method="post">
            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="customer_name">Customer Name</label>
                    <input type="text" list="customersList" name="customer_name" id="customer_name" class="form-control" placeholder="Enter customer name or select from list">
                    <datalist id="customersList">
                        <?php foreach ($customersData as $customer): ?>
                            <option value="<?= htmlspecialchars($customer['name']); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <hr>
                <div id="sale-items-container">
                    <div class="row sale-item-row mb-3 align-items-end">
                        <div class="col-md-5">
                            <label>Product</label>
                            <input type="text" list="productsList" class="form-control product-name-input" placeholder="Search Product" required>
                            <datalist id="productsList">
                                <?php foreach ($medicinesData as $medicine): ?>
                                    <option value="<?= htmlspecialchars($medicine['product_name']); ?>" data-stock-id="<?= htmlspecialchars($medicine['stock_id']); ?>" data-quantity="<?= htmlspecialchars($medicine['quantity']); ?>" data-sale-price="<?= htmlspecialchars($medicine['sale_price']); ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <input type="hidden" name="stock_id[]" class="stock-id-input">
                            <input type="hidden" name="product_name[]" class="product-name-input-hidden">
                            <small class="text-muted stock-info"></small>
                        </div>
                        <div class="col-md-2">
                            <label>Unit Price</label>
                            <input type="number" step="0.01" name="unit_price[]" class="form-control unit-price-input" required readonly>
                        </div>
                        <div class="col-md-2">
                            <label>Quantity</label>
                            <input type="number" name="quantity[]" class="form-control quantity-input" value="1" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <label>Total</label>
                            <input type="number" step="0.01" name="total[]" class="form-control total-input" required readonly>
                        </div>
                        <div class="col-md-1 d-flex justify-content-end">
                            <button type="button" class="btn btn-danger remove-row" style="display: none;">-</button>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12 text-right">
                        <button type="button" class="btn btn-secondary" id="add-item">Add Item</button>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <div class="d-flex justify-content-between">
                            <h4>Grand Total:</h4>
                            <h4><span id="grand-total-display">0.00</span></h4>
                            <input type="hidden" name="grand_total" id="grand-total-input">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="create_sale" class="btn btn-success float-right">Complete Sale</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
class SaleManager {
    constructor() {
        this.container = $('#sale-items-container');
        this.addItemButton = $('#add-item');
        this.grandTotalDisplay = $('#grand-total-display');
        this.grandTotalInput = $('#grand-total-input');
        this.bindEvents();
    }

    bindEvents() {
        this.container.on('input', '.product-name-input', this.handleProductInput.bind(this));
        this.container.on('input', '.quantity-input', this.handleQuantityChange.bind(this));
        this.container.on('click', '.remove-row', this.removeRow.bind(this));
        this.addItemButton.on('click', this.addNewRow.bind(this));
    }

    handleProductInput(e) {
        const input = $(e.target);
        const row = input.closest('.sale-item-row');
        const value = input.val();
        const option = $('#productsList option[value="' + value + '"]');

        if (option.length) {
            const product = {
                stock_id: option.data('stock-id'),
                quantity: option.data('quantity'),
                sale_price: option.data('sale-price')
            };
            this.updateRow(row, product);
        } else {
            this.clearRow(row);
        }
    }

    handleQuantityChange(e) {
        const input = $(e.target);
        const row = input.closest('.sale-item-row');
        const quantity = parseInt(input.val()) || 0;
        const availableStock = parseInt(row.find('.product-name-input').get(0).list.options[0].dataset.quantity) || 0;

        if (quantity > availableStock) {
            input.val(availableStock);
            alert('Cannot sell more than available stock.');
        }
        
        this.updateRowTotals(row);
    }

    updateRow(row, product) {
        const qtyInput = row.find('.quantity-input');
        const unitPriceInput = row.find('.unit-price-input');
        const stockIdInput = row.find('.stock-id-input');
        const totalInput = row.find('.total-input');
        const stockInfo = row.find('.stock-info');

        let qty = parseInt(qtyInput.val()) || 1;
        if (qty > product.quantity) {
            qty = product.quantity;
            qtyInput.val(qty);
        }
        if (product.quantity > 0) {
            stockInfo.text(`In stock: ${product.quantity}`);
        }

        unitPriceInput.val(parseFloat(product.sale_price).toFixed(2));
        totalInput.val((qty * product.sale_price).toFixed(2));
        stockIdInput.val(product.stock_id);
        this.updateTotal();
    }

    clearRow(row) {
        row.find('.unit-price-input').val('');
        row.find('.quantity-input').val('1');
        row.find('.total-input').val('');
        row.find('.stock-id-input').val('');
        row.find('.stock-info').text('');
        this.updateTotal();
    }

    updateRowTotals(row) {
        const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
        const unitPrice = parseFloat(row.find('.unit-price-input').val()) || 0;
        const total = quantity * unitPrice;
        row.find('.total-input').val(total.toFixed(2));
        this.updateTotal();
    }

    removeRow(e) {
        $(e.target).closest('.sale-item-row').remove();
        this.updateTotal();
    }

    addNewRow() {
        const firstRow = this.container.find('.sale-item-row').first();
        if (firstRow.length) {
            const newRow = firstRow.clone(true); // true to copy events
            newRow.find('input').val('');
            newRow.find('.quantity-input').val('1');
            newRow.find('.stock-info').text('');
            newRow.find('.remove-row').show();
            this.container.append(newRow);
            this.updateTotal();
        }
    }

    updateTotal() {
        let total = 0;
        this.container.find('[name="total[]"]').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        this.grandTotalDisplay.text(total.toFixed(2));
        this.grandTotalInput.val(total.toFixed(2));
    }
}

$(function() {
    new SaleManager();
});
</script>