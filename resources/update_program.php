<?php
require_once '../includes/db.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE programs SET 
            program_name = ?,
            program_type = ?,
            program_description = ?,
            target_beneficiaries = ?
            WHERE id = ?");
        
        $stmt->execute([
            $_POST['program_name'],
            $_POST['program_type'],
            $_POST['program_description'],
            $_POST['target_beneficiaries'],
            $_POST['program_id']
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Program updated successfully'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update program: ' . $e->getMessage()
        ]);
    }
}