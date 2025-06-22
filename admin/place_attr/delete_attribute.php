<?php
require_once '../../includes/db.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];
        
        $pdo->beginTransaction();
        
        // Delete the attribute
        $stmt = $pdo->prepare("DELETE FROM barangay_sitio_attributes WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log the activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (act_name, act_type) VALUES (?, ?)");
        $log_stmt->execute([
            "Deleted place attribute ID: " . $id,
            "place_attribute_delete"
        ]);
        
        $pdo->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Attribute deleted successfully'
        ]);
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
}