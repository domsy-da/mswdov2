<?php
include '../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM resources WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: index.php');
exit;
?>
