<?php
include __DIR__ . '/../../config.php'; // Correct path

if(!isset($_GET['id'])) die("Invalid request!");
$id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();

header("Location: ../../home.php?page=2");
exit;
?>