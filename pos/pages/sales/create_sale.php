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
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add New Sale</h3>
        </div>
        <div class="card-body">
            <form action="home.php?page=11" method="POST" id="saleForm">
                <div class="form-group mb-3">
                    <label for="customer_name">Customer Name</label>
                    <input type="text" name="customer_name" id="customer_name" class="form-control" list="customer-list" placeholder="Start typing or select a customer">
                    <datalist id="customer-list">
                        <?php foreach ($customersData as $customer): ?>
                            <option value="<?= htmlspecialchars($customer['name']); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <hr>
                
                <div id="sale-items-container">
                    <div class="row sale-item-row mb-3">
                        <div class="col-md-4">
                            <label>Product Name</label>
                            <input type="text" class="form-control product-name-input" name="product_name[]" list="product-list" required>
                        </div>
                        <div class="col-md-2">
                            <label>Unit Price</label>
                            <input type="number" step="0.01" class="form-control unit-price-input" name="unit_price[]" required readonly>
                        </div>
                        <div class="col-md-2">
                            <label>Quantity</label>
                            <input type="number" class="form-control quantity-input" name="quantity[]" min="1" value="1" required>
                            <small class="text-info stock-info"></small>
                        </div>
                        <div class="col-md-2">
                            <label>Total</label>
                            <input type="number" step="0.01" class="form-control total-input" name="total[]" required readonly>
                        </div>
                        <div class="col-md-2">
                            <label style="color: transparent;">Action</label>
                            <button type="button" class="btn btn-danger btn-block sale-delete-btn"><i class="fas fa-trash"></i></button>
                        </div>
                        <input type="hidden" name="stock_id[]" class="stock-id-input">
                    </div>
                </div>

                <datalist id="product-list">
                    <?php foreach ($medicinesData as $medicine): ?>
                        <option value="<?= htmlspecialchars($medicine['product_name']); ?>" data-stock-id="<?= htmlspecialchars($medicine['stock_id']); ?>" data-sale-price="<?= htmlspecialchars($medicine['sale_price']); ?>" data-quantity="<?= htmlspecialchars($medicine['quantity']); ?>">
                    <?php endforeach; ?>
                </datalist>
                
                <div class="row mt-3">
                    <div class="col-md-12 text-right">
                        <button type="button" id="add-item-btn" class="btn btn-primary"><i class="fas fa-plus"></i> Add Item</button>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6 offset-md-6">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>Grand Total:</h4>
                            <input type="text" name="grand_total" id="grand-total" class="form-control form-control-lg text-right" style="width: 50%; font-weight: bold;" value="0.00" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="create_sale" class="btn btn-success btn-block btn-lg">Complete Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
class SaleManager {
    constructor() {
        this.container = document.getElementById('sale-items-container');
        this.totalInput = document.getElementById('grand-total');
        this.medicinesData = <?= json_encode($medicinesData); ?>;
        this.addEventListeners();
        this.updateTotal();
    }

    addEventListeners() {
        document.getElementById('add-item-btn').addEventListener('click', () => this.addNewRow());
        this.container.addEventListener('input', (e) => this.handleRowUpdate(e));
        this.container.addEventListener('click', (e) => this.handleDelete(e));
    }

    handleRowUpdate(e) {
        if (e.target.matches('.product-name-input, .quantity-input')) {
            const row = e.target.closest('.sale-item-row');
            this.updateRow(row);
        }
    }

    handleDelete(e) {
        if (e.target.closest('.sale-delete-btn')) {
            const row = e.target.closest('.sale-item-row');
            if (this.container.querySelectorAll('.sale-item-row').length > 1) {
                row.remove();
                this.updateTotal();
            } else {
                alert('You must have at least one product in the sale.');
            }
        }
    }

    updateRow(row) {
        const productInput = row.querySelector('.product-name-input');
        const qtyInput = row.querySelector('.quantity-input');
        const unitPriceInput = row.querySelector('.unit-price-input');
        const totalInput = row.querySelector('.total-input');
        const stockIdInput = row.querySelector('.stock-id-input');
        const stockInfo = row.querySelector('.stock-info');

        const productName = productInput.value;
        const product = this.medicinesData.find(m => m.product_name === productName);
        
        unitPriceInput.value = "";
        totalInput.value = "";
        stockIdInput.value = "";
        stockInfo.textContent = "";

        if (!product) {
            this.updateTotal();
            return;
        }

        let qty = parseInt(qtyInput.value) || 1;
        if (qty > product.quantity) {
            qty = product.quantity;
            qtyInput.value = qty;
        }
        if (product.quantity > 0) {
            stockInfo.textContent = `In stock: ${product.quantity}`;
        }

        unitPriceInput.value = parseFloat(product.sale_price).toFixed(2);
        totalInput.value = (qty * product.sale_price).toFixed(2);
        stockIdInput.value = product.stock_id;
        this.updateTotal();
    }

    addNewRow() {
        const firstRow = this.container.querySelector('.sale-item-row');
        if (firstRow) {
            const newRow = firstRow.cloneNode(true);
            newRow.querySelector('.product-name-input').value = "";
            newRow.querySelector('.unit-price-input').value = "";
            newRow.querySelector('.quantity-input').value = "1";
            newRow.querySelector('.total-input').value = "";
            newRow.querySelector('.stock-id-input').value = "";
            newRow.querySelector('.stock-info').textContent = "";
            this.container.appendChild(newRow);
            this.updateTotal();
        }
    }

    updateTotal() {
        let total = 0;
        this.container.querySelectorAll('[name="total[]"]').forEach(inp => total += parseFloat(inp.value) || 0);
        this.totalInput.value = total.toFixed(2);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new SaleManager();
});
</script>