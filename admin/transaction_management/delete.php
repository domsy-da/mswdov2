<?php
require_once '../../includes/db.php';

// Validate if ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        // Prepare delete query securely
        $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Redirect back with success
            header("Location: index.php?msg=deleted");
            exit;
        } else {
            echo "Error: Unable to delete transaction.";
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    echo "Invalid transaction ID.";
}
?>
