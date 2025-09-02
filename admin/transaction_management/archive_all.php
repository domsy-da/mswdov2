<?php
require_once '../../includes/db.php';

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Insert from transactions into combined_transactions (only matching columns)
    $sql = "INSERT INTO combined_transactions 
        (id, beneficiary_id, client_name, client_age, amount, client_sitio, client_barangay, created_at) 
        SELECT id, beneficiary_id, client_name, client_age, amount, client_sitio, client_barangay, NOW()
        FROM transactions";

    $pdo->exec($sql);

    // Delete from transactions after successful insert
    $pdo->exec("DELETE FROM transactions");

    // Commit
    $pdo->commit();

    header("Location: index.php?msg=Archived successfully");
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error archiving: " . $e->getMessage());
}
