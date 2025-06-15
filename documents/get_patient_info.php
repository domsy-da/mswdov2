<?php
require_once '../includes/db_connection.php'; // Ensure this file initializes a PDO connection

header("Content-Type: application/json");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $request_type = "client_requesting";

    try {
        // Prepare SQL statement to fetch the latest patient transaction for this beneficiary
        $sql = "SELECT * FROM transactions WHERE beneficiary_id = ? AND request_type = ? ORDER BY request_date DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id,$request_type]);  // Execute with parameter
        
        // Fetch the result
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($patient) {
            echo json_encode([
                "success" => true,
                "patient_name" => $patient['patient_name'],
                "relation" => $patient['relation'],
                "patient_age" => $patient['patient_age'],
                "patient_gender" => $patient['patient_gender'],
                "patient_civil_status" => $patient['patient_civil_status'],
                "patient_birthday" => $patient['patient_birthday'],
                "patient_birthplace" => $patient['patient_birthplace'],
                "patient_education" => $patient['patient_education'],
                "patient_occupation" => $patient['patient_occupation'],
                "patient_religion" => $patient['patient_religion'],
                "patient_address" => $patient['patient_address'],
                "amount" => $patient['amount'],
                "diagnosis_school" => $patient['diagnosis_school'],
                "prep_by" => $patient['prep_by'],
                "pos_prep" => $patient['pos_prep'],
                "not_by" => $patient['not_by'],
                "pos_not" => $patient['pos_not'],
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "No patient record found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
