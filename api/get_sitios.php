<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['barangay_id'])) {
        throw new Exception('Barangay ID is required');
    }

    $barangayId = (int)$_GET['barangay_id'];
    
    $stmt = $pdo->prepare("
        SELECT id, name 
        FROM sitios 
        WHERE barangay_id = ?
        ORDER BY name ASC
    ");
    
    $stmt->execute([$barangayId]);
    $sitios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($sitios);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}