<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        
        $stmt = $pdo->prepare("DELETE FROM beneficiaries WHERE id = ?");
        $success = $stmt->execute([$id]);
        
        echo json_encode(['success' => $success]);
    } else {
        throw new Exception('Invalid request');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}