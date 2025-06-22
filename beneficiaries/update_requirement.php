<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['beneficiary_id']) || !isset($input['requirements'])) {
        throw new Exception('Missing required data');
    }

    $beneficiary_id = (int)$input['beneficiary_id'];
    $requirements = $input['requirements'];

    $pdo->beginTransaction();

    foreach ($requirements as $req) {
        $requirement_id = (int)$req['requirement_id'];
        $is_submitted = $req['is_submitted'] ? 1 : 0;

        // Check if requirement already exists
        $stmt = $pdo->prepare("SELECT id FROM beneficiary_requirements 
                              WHERE beneficiary_id = ? AND requirement_id = ?");
        $stmt->execute([$beneficiary_id, $requirement_id]);
        
        if ($stmt->fetch()) {
            // Update existing record
            $stmt = $pdo->prepare("UPDATE beneficiary_requirements 
                                  SET is_submitted = ?, date_submitted = CURRENT_TIMESTAMP 
                                  WHERE beneficiary_id = ? AND requirement_id = ?");
            $stmt->execute([$is_submitted, $beneficiary_id, $requirement_id]);
        } else {
            // Insert new record
            $stmt = $pdo->prepare("INSERT INTO beneficiary_requirements 
                                  (beneficiary_id, requirement_id, is_submitted, date_submitted) 
                                  VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
            $stmt->execute([$beneficiary_id, $requirement_id, $is_submitted]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}