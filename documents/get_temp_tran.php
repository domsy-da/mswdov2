<?php
require_once '../includes/db.php';

$beneficiary_id = isset($_GET['beneficiary_id']) ? $_GET['beneficiary_id'] : null;

if (!$beneficiary_id) {
    echo json_encode(['success' => false, 'error' => 'No beneficiary_id provided']);
    exit;
}

try {
    // Get the latest temp_tran for this beneficiary
    $stmt = $pdo->prepare("SELECT * FROM temp_tran WHERE beneficiary_id = ? ORDER BY created_at DESC, id DESC LIMIT 1");
    $stmt->execute([$beneficiary_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($transaction) {
        echo json_encode(['success' => true, 'transaction' => $transaction]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No transaction found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}