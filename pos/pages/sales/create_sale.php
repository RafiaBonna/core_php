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
        if ($customer_name !== '') {
            $stmt = $conn->prepare("SELECT id FROM customers WHERE name = ?");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("s", $customer_name);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $customer = $result->fetch_assoc();
                $customer_id = $customer['id'];
            } else {
                $stmt = $conn->prepare("INSERT INTO customers (name) VALUES (?)");
                if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
                $stmt->bind_param("s", $customer_name);
                $stmt->execute();
                $customer_id = $conn->insert_id;
            }
            $stmt->close();
        }

        // Insert sale record
        $stmt = $conn->prepare("INSERT INTO sales (customer_id, total_amount) VALUES (?, ?)");
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
        $stmt->bind_param("id", $customer_id, $total_amount);
        $stmt->execute();
        $sales_id = $conn->insert_id;
        $stmt->close();

        // Loop through products and insert sale items, and update stock
        foreach ($stock_ids as $key => $stock_id) {
            $quantity = intval($quantities[$key]);
            if ($quantity <= 0) continue;

            // Insert into sale_items
            $stmt_item = $conn->prepare("INSERT INTO sale_items (sales_id, product_id, quantity) VALUES (?, ?, ?)");
            if (!$stmt_item) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_item->bind_param("iii", $sales_id, $stock_id, $quantity);
            $stmt_item->execute();
            $stmt_item->close();

            // Update stock quantity
            $stmt_stock = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE id = ?");
            if (!$stmt_stock) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_stock->bind_param("ii", $quantity, $stock_id);
            $stmt_stock->execute();
            $stmt_stock->close();
        }

        $conn->commit();
        $message = "<div class='alert alert-success'>Sale recorded successfully! Invoice ID: " . $sales_id . "</div>";

    } catch (Exception $e) {
        $conn->rollback();
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<style>
    .form-group label {
        font-weight: 500;
    }
    .card-body {
        padding: 2rem;
    }
    .btn-add-item {
        margin-top: 15px;
    }
    .sale-delete-btn {
        background: none;
        border: none;
        color: #dc3545;
        font-size: 1.25rem;
        cursor: pointer;
    }
    .sale-delete-btn:hover {
        color: #c82333;
    }
    #grand_total {
        font-size: 1.5rem;
        font-weight: bold;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Create New Sale</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">New Sale</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <?php echo $message; ?>
            <form method="post" id="saleForm">
                <div class="mb-3">
                    <label for="customer_name" class="form-label">Customer Name</label>
                    <input type="text" list="customers" id="customer_name" name="customer_name" class="form-control" placeholder="Enter customer name or select existing">
                    <datalist id="customers">
                        <?php foreach ($customersData as $customer): ?>
                            <option value="<?= htmlspecialchars($customer['name']); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div id="sale-items-container">
                    <!-- Dynamic rows will be added here by JavaScript -->
                </div>

                <button type="button" id="addItemBtn" class="btn btn-primary btn-add-item">Add Item</button>

                <div class="d-flex justify-content-end align-items-center mt-4">
                    <h4 class="me-3">Grand Total:</h4>
                    <input type="text" id="grand_total" name="grand_total" class="form-control text-right" readonly value="0.00" style="width: 150px;">
                </div>

                <button type="submit" class="btn btn-success btn-block mt-4">Process Sale</button>
            </form>
        </div>
    </div>
</div>

<datalist id="products">
    <?php foreach ($medicinesData as $medicine): ?>
        <option value="<?= htmlspecialchars($medicine['product_name']); ?>"
                data-stock-id="<?= htmlspecialchars($medicine['stock_id']); ?>"
                data-sale-price="<?= htmlspecialchars($medicine['sale_price']); ?>"
                data-quantity="<?= htmlspecialchars($medicine['quantity']); ?>">
    <?php endforeach; ?>
</datalist>

<script>
class SaleForm {
    constructor() {
        this.container = document.getElementById('sale-items-container');
        this.addItemBtn = document.getElementById('addItemBtn');
        this.totalInput = document.getElementById('grand_total');
        this.productsDatalist = document.getElementById('products');
        this.medicines = this.getMedicinesData();

        this.addItemBtn.addEventListener('click', () => this.addItem());
        this.addItem(); // Add one row by default on page load
    }

    getMedicinesData() {
        const medicines = [];
        this.productsDatalist.querySelectorAll('option').forEach(option => {
            medicines.push({
                stock_id: option.dataset.stockId,
                product_name: option.value,
                sale_price: option.dataset.salePrice,
                quantity: parseInt(option.dataset.quantity)
            });
        });
        return medicines;
    }

    addItem() {
        const row = document.createElement('div');
        row.classList.add('row', 'mb-3', 'align-items-end');
        row.innerHTML = `
            <div class="col-md-5">
                <div class="form-group">
                    <label for="product_name">Product Name</label>
                    <input type="text" name="product_name[]" class="form-control product-input" list="products" required>
                    <input type="hidden" name="stock_id[]" class="stock-id-input">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="unit_price">Unit Price</label>
                    <input type="number" name="unit_price[]" class="form-control unit-price-input" step="0.01" readonly>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="quantity">Qty</label>
                    <input type="number" name="quantity[]" class="form-control quantity-input" min="1" value="1" required>
                    <small class="text-muted stock-info"></small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="total">Total</label>
                    <input type="number" name="total[]" class="form-control total-input" step="0.01" readonly>
                </div>
            </div>
            <div class="col-md-1 d-flex justify-content-center">
                <button type="button" class="sale-delete-btn" aria-label="Delete item">&times;</button>
            </div>
        `;

        const productInput = row.querySelector('.product-input');
        const qtyInput = row.querySelector('.quantity-input');
        const unitPriceInput = row.querySelector('.unit-price-input');
        const totalInput = row.querySelector('.total-input');
        const stockIdInput = row.querySelector('.stock-id-input');
        const stockInfo = row.querySelector('.stock-info');

        const updateRow = () => {
            const productName = productInput.value.trim().toLowerCase();
            const product = this.medicines.find(m => m.product_name.toLowerCase() === productName);
            
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
        };

        productInput.addEventListener('input', updateRow);
        qtyInput.addEventListener('input', updateRow);
        row.querySelector('.sale-delete-btn').addEventListener('click', () => {
            row.remove();
            this.updateTotal();
        });

        this.container.appendChild(row);
    }

    updateTotal() {
        let total = 0;
        this.container.querySelectorAll('[name="total[]"]').forEach(inp => total += parseFloat(inp.value) || 0);
        this.totalInput.value = total.toFixed(2);
    }
}

window.addEventListener('DOMContentLoaded', () => {
    new SaleForm();
});
</script>
