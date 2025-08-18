<?php
include __DIR__ . '/../../config.php';

if (!isset($_GET['id'])) die("Invalid request!");
$id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM products WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: ../../home.php?page=8");
exit;
?>