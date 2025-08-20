<?php
include __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['move_to_expired'])) {

    // Get POST data safely
    $product_id = intval($_POST['product_id'] ?? 0);
    $product_name = trim($_POST['product_name'] ?? '');
    $stock_to_move = intval($_POST['stock_to_move'] ?? 0);
    $expiry_date = $_POST['expiry_date'] ?? '';

    // Validate data
    if (!$product_id || !$product_name || !$stock_to_move || !$expiry_date) {
        header("Location: ../../home.php?page=expired_products&status=error&message=Missing required data");
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert into expired_products
        $stmt_insert = $conn->prepare("INSERT INTO expired_products (product_id, product_name, quantity_expired, expiry_date) VALUES (?, ?, ?, ?)");
        if (!$stmt_insert) throw new Exception("Insert Prepare Failed: " . $conn->error);

        $stmt_insert->bind_param("isis", $product_id, $product_name, $stock_to_move, $expiry_date);

        if (!$stmt_insert->execute()) throw new Exception("Insert Execute Failed: " . $stmt_insert->error);
        $stmt_insert->close();

        // Delete from products
        $stmt_delete = $conn->prepare("DELETE FROM products WHERE id = ?");
        if (!$stmt_delete) throw new Exception("Delete Prepare Failed: " . $conn->error);

        $stmt_delete->bind_param("i", $product_id);

        if (!$stmt_delete->execute()) throw new Exception("Delete Execute Failed: " . $stmt_delete->error);
        $stmt_delete->close();

        // Commit transaction
        $conn->commit();

        // Redirect back with success message
        header("Location: ../../home.php?page=expired_products&status=success");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../../home.php?page=expired_products&status=error&message=" . urlencode($e->getMessage()));
        exit;
    }

} else {
    die("Invalid request.");
}
