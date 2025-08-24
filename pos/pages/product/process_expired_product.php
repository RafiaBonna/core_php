<?php
include __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['move_to_expired'])) {
    $id = intval($_POST['id']);
    $product_name = trim($_POST['product_name']);
    $stock_to_move = intval($_POST['stock_to_move']);
    $expiry_date = $_POST['expiry_date'];

    if (!$id || !$product_name || !$stock_to_move || !$expiry_date) {
        header("Location: ../../home.php?page=expired_products&status=error&message=Missing required data");
        exit;
    }

    $conn->begin_transaction();
    try {
        // Step 1: Insert into expired_products
        $stmt = $conn->prepare("INSERT INTO expired_products (id, product_name, quantity_expired, expiry_date) 
                                VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $id, $product_name, $stock_to_move, $expiry_date);
        $stmt->execute();
        $stmt->close();

        // Step 2: Delete from products
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

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
