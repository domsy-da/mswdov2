<?php
require_once '../includes/db.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];
        
        $pdo->beginTransaction();
        
        // Check if program exists and get program details
        $check_stmt = $pdo->prepare("SELECT program_name FROM programs WHERE id = ?");
        $check_stmt->execute([$id]);
        $program = $check_stmt->fetch();
        
        if (!$program) {
            throw new Exception("Program not found");
        }

        // Delete the program
        $delete_stmt = $pdo->prepare("DELETE FROM programs WHERE id = ?");
        $delete_stmt->execute([$id]);

        // Log the activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (act_name, act_type) VALUES (?, ?)");
        $log_stmt->execute([
            "Deleted program: " . $program['program_name'],
            "program_delete"
        ]);
        
        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Program deleted successfully'
        ]);
        
    } catch (Exception $e) {
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