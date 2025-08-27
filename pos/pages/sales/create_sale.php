<?php
// Include database connection
include_once __DIR__ . '/../../config.php';

// Fetch products
$medicinesData = [];
$medicines = $conn->query("SELECT id AS stock_id, product_name, sale_price, quantity FROM stock WHERE quantity > 0");
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $total_amount = floatval($_POST['grand_total'] ?? 0);
    $stock_ids = $_POST['stock_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $unit_prices = $_POST['unit_price'] ?? [];

    if (!empty($stock_ids)) {
        $conn->begin_transaction();
        try {
            // Handle customer
            $customer_id = null;
            if ($customer_name !== '') {
                $stmt = $conn->prepare("SELECT id FROM customers WHERE name = ?");
                $stmt->bind_param("s", $customer_name);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $customer_id = $row['id'];
                } else {
                    $stmt = $conn->prepare("INSERT INTO customers (name, created_at, updated_at) VALUES (?, NOW(), NOW())");
                    $stmt->bind_param("s", $customer_name);
                    $stmt->execute();
                    $customer_id = $stmt->insert_id;
                }
            }

            // Insert sale
            $stmt_sale = $conn->prepare("INSERT INTO sales (customer_id, total_amount, sale_date) VALUES (?, ?, NOW())");
            $stmt_sale->bind_param("id", $customer_id, $total_amount);
            $stmt_sale->execute();
            $sale_id = $conn->insert_id;

            // Insert sale items and update stock
            for ($i = 0; $i < count($stock_ids); $i++) {
                $sid = intval($stock_ids[$i]);
                $qty = intval($quantities[$i]);
                $price = floatval($unit_prices[$i]);
                $total_price = $qty * $price;

                $stmt_check = $conn->prepare("SELECT quantity FROM stock WHERE id = ?");
                $stmt_check->bind_param("i", $sid);
                $stmt_check->execute();
                $available_stock = $stmt_check->get_result()->fetch_assoc()['quantity'];

                if ($qty > $available_stock) throw new Exception("Insufficient stock for product ID: $sid");

                $stmt_item = $conn->prepare("INSERT INTO sale_items (sale_id, stock_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
                $stmt_item->bind_param("iiidd", $sale_id, $sid, $qty, $price, $total_price);
                $stmt_item->execute();

                $stmt_update = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE id = ?");
                $stmt_update->bind_param("ii", $qty, $sid);
                $stmt_update->execute();
            }

            $conn->commit();
            // header("Location: sales_history.php?sale_id=$sale_id");
            // exit;

        } catch (Exception $e) {
            $conn->rollback();
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Please add at least one product.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Sales Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
    <h2 class="mb-4">Quick Sales Entry</h2>
    <form id="salesForm" method="POST">
        <!-- Customer -->
        <div class="mb-3">
            <label for="customer_name" class="form-label">Customer Name</label>
            <input list="customerList" name="customer_name" id="customer_name" class="form-control" placeholder="Type or select">
            <datalist id="customerList">
                <?php foreach ($customersData as $c): ?>
                    <option value="<?= htmlspecialchars($c['name']); ?>"></option>
                <?php endforeach; ?>
            </datalist>
        </div>

        <!-- Products -->
        <h5 class="mt-4">Products</h5>
        <div id="medicine-container" class="mb-3"></div>
        <button type="button" class="btn btn-sm btn-primary mb-3 add-medicine-btn">+ Add Product</button>

        <!-- Grand Total -->
        <div class="mb-3">
            <label for="grand-total" class="form-label">Grand Total</label>
            <input type="number" id="grand-total" name="grand_total" class="form-control" readonly value="0.00">
        </div>

        <button type="submit" class="btn btn-success">Confirm Sale</button>
    </form>
</div>

<datalist id="medicineList">
    <?php foreach ($medicinesData as $m): ?>
        <option value="<?= htmlspecialchars($m['product_name']); ?>"
                data-id="<?= $m['stock_id']; ?>"
                data-qty="<?= $m['quantity']; ?>"
                data-price="<?= $m['sale_price']; ?>"></option>
    <?php endforeach; ?>
</datalist>

<script>
const medicines = <?php echo json_encode($medicinesData); ?>;

class SalesForm {
    constructor(formId, containerId, totalId, medicines) {
        this.form = document.getElementById(formId);
        this.container = document.getElementById(containerId);
        this.totalInput = document.getElementById(totalId);
        this.medicines = medicines;
        this.init();
    }

    init() {
        this.addRow();
        document.querySelector('.add-medicine-btn').addEventListener('click', () => this.addRow());

        this.form.addEventListener('submit', (e) => {
            if (!confirm('Confirm this sale?')) e.preventDefault();
        });
    }

    addRow() {
        const row = document.createElement('div');
        row.className = 'row g-3 align-items-end mb-2';
        row.innerHTML = `
            <div class="col-md-4">
                <label class="form-label">Product</label>
                <input list="medicineList" name="product_name[]" class="form-control" required>
                <input type="hidden" name="stock_id[]">
            </div>
            <div class="col-md-2">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity[]" class="form-control" min="1" value="1">
            </div>
            <div class="col-md-2">
                <label class="form-label">Unit Price</label>
                <input type="number" name="unit_price[]" class="form-control" step="0.01" value="0.00" readonly>
            </div>
            <div class="col-md-2">
                <label class="form-label">Total</label>
                <input type="number" name="total[]" class="form-control" step="0.01" value="0.00" readonly>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm sale-delete-btn">❌</button>
            </div>
        `;

        const productInput = row.querySelector('[name="product_name[]"]');
        const qtyInput = row.querySelector('[name="quantity[]"]');
        const unitPriceInput = row.querySelector('[name="unit_price[]"]');
        const totalInput = row.querySelector('[name="total[]"]');
        const stockIdInput = row.querySelector('[name="stock_id[]"]');

        const updateRow = () => {
            const productName = productInput.value.trim().toLowerCase();
            const product = this.medicines.find(m => m.product_name.toLowerCase() === productName);
            if (!product) {
                unitPriceInput.value = "0.00";
                totalInput.value = "0.00";
                stockIdInput.value = "";
                this.updateTotal();
                return;
            }
            let qty = parseFloat(qtyInput.value) || 1;
            if (qty > product.quantity) qty = product.quantity;
            qtyInput.value = qty;

            unitPriceInput.value = parseFloat(product.sale_price).toFixed(2);
            totalInput.value = (qty * product.sale_price).toFixed(2);
            stockIdInput.value = product.stock_id;
            this.updateTotal();
        };

        productInput.addEventListener('change', updateRow);
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
    new SalesForm('salesForm', 'medicine-container', 'grand-total', medicines);
});
</script>
</body>
</
