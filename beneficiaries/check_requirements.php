<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $beneficiary_id = isset($_GET['beneficiary_id']) ? (int)$_GET['beneficiary_id'] : 0;
    $service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
    
    if (!$beneficiary_id || !$service_id) {
        throw new Exception('Missing beneficiary ID or service ID');
    }
    
    // Get all requirements for the specific service
    $stmt = $pdo->prepare("
        SELECT r.id, r.requirement_name 
        FROM requirements r
        WHERE r.service_id = ?
    ");
    $stmt->execute([$service_id]);
    $all_requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get submitted requirements for this beneficiary and service
    $stmt = $pdo->prepare("
        SELECT r.id, r.requirement_name 
        FROM requirements r
        INNER JOIN beneficiary_requirements br ON r.id = br.requirement_id 
        WHERE br.beneficiary_id = ? 
        AND r.service_id = ?
        AND br.is_submitted = 1
    ");
    $stmt->execute([$beneficiary_id, $service_id]);
    $submitted_requirements = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get missing requirements
    $missing_requirements = array();
    foreach ($all_requirements as $req) {
        if (!in_array($req['id'], $submitted_requirements)) {
            $missing_requirements[] = $req['requirement_name'];
        }
    }
    
    $is_complete = empty($missing_requirements);
    
    echo json_encode([
        'success' => true,
        'complete' => $is_complete,
        'missing' => $missing_requirements,
        'service_id' => $service_id
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'complete' => false,
        'missing' => []
    ]);
}
?>