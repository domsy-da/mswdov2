<?php

require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    // GET request for fetching relative details
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM relatives WHERE id = ?");
        $stmt->execute([$id]);
        $relative = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($relative) {
            echo json_encode(['success' => true, 'relative' => $relative]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Relative not found']);
        }
        exit;
    }

    // POST request for add/edit/delete
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle delete action
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM relatives WHERE id = ?");
            $success = $stmt->execute([$id]);
            
            echo json_encode(['success' => $success]);
            exit;
        }

        // Handle add/edit action
        $id = isset($_POST['relative_id']) && !empty($_POST['relative_id']) ? (int)$_POST['relative_id'] : null;
        
        $data = [
            'beneficiary_id' => $_POST['beneficiary_id'],
            'name' => $_POST['name'],
            'age' => $_POST['age'],
            'civil_status' => $_POST['civil_status'],
            'relationship' => $_POST['relationship'],
            'educational_attainment' => !empty($_POST['educational_attainment']) ? $_POST['educational_attainment'] : null,
            'occupation' => !empty($_POST['occupation']) ? $_POST['occupation'] : null
        ];

        if ($id) {
            // Update
            $sql = "UPDATE relatives SET 
                    beneficiary_id = ?, name = ?, age = ?, civil_status = ?, 
                    relationship = ?, educational_attainment = ?, occupation = ? 
                    WHERE id = ?";
            $data[] = $id;
        } else {
            // Insert
            $sql = "INSERT INTO relatives 
                    (beneficiary_id, name, age, civil_status, relationship, educational_attainment, occupation) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        }

        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute(array_values($data));
        
        echo json_encode([
            'success' => $success,
            'message' => $id ? 'Relative updated successfully' : 'Relative added successfully'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>