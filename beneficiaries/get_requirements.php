<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
    $beneficiary_id = isset($_GET['beneficiary_id']) ? (int)$_GET['beneficiary_id'] : 0;

    // Get requirements for the selected service
    $stmt = $pdo->prepare("
        SELECT 
            r.id,
            r.requirement_name,
            COALESCE(br.is_submitted, 0) as is_submitted
        FROM requirements r
        LEFT JOIN beneficiary_requirements br ON r.id = br.requirement_id 
            AND br.beneficiary_id = ?
        WHERE r.service_id = ?
    ");
    
    $stmt->execute([$beneficiary_id, $service_id]);
    $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'requirements' => $requirements
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}