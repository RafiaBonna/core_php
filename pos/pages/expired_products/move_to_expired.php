<?php
include_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['move_to_expired'])) {
    $stock_id = intval($_POST['stock_id']);
    $quantity_expired = intval($_POST['quantity']);
    $expiry_date = $_POST['expiry_date'];

    if (!$stock_id || !$quantity_expired || !$expiry_date) {
        header("Location: stock_view.php?status=error&message=Missing data");
        exit;
    }

    $conn->begin_transaction();

    try {
        // Step 1: Insert into expired_products
        $stmt = $conn->prepare("INSERT INTO expired_products (stock_id, quantity_expired, expiry_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $stock_id, $quantity_expired, $expiry_date);
        $stmt->execute();
        $stmt->close();

        // Step 2: Update stock quantity to 0
        $stmt = $conn->prepare("UPDATE stock SET quantity = 0 WHERE id = ?");
        $stmt->bind_param("i", $stock_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        header("Location: stock_view.php?status=success&message=Product moved to expired successfully!");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: stock_view.php?status=error&message=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: stock_view.php");
    exit;
}
