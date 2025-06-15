<?php
// Turn off error display - add this at the very top
error_reporting(0);
ini_set('display_errors', 0);

// Ensure we're sending JSON response headers first
header('Content-Type: application/json');

// Check for database connection
if (!include_once('../includes/db_connection.php')) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Retrieve POST data
    $beneficiary_id = $_POST['id'];
    $request_purpose = $_POST['request_purpose'];
    function str_request_purpose($request_purpose) {
        switch ($request_purpose) {
            case 'med_exp':
                return "Medical Expense";
            case 'hos_bill':
                return "Hospital Bill";
            case 'hos_bill_hel':
                return "Hospital Bill";
            case 'med_exp_hel':
                return "Medical Expense";
            case 'burial':
                return "Burial";
            case 'educational':
                return "Educational";
            default:
                return "Unknown";
        }
    }
    $formatted_purpose = str_request_purpose($request_purpose);
    $patient_name = $_POST['patient_name'];
    $patient_age = $_POST['patient_age'];
    $relation = $_POST['relation'];
    $patient_gender = $_POST['patient_gender'];
    $patient_civil_status = $_POST['patient_civil_status'];
    $patient_birthday = $_POST['patient_birthday'];
    $patient_birthplace = $_POST['patient_birthplace'];
    $patient_education = $_POST['patient_education'];
    $patient_occupation = $_POST['patient_occupation'];
    $patient_religion = $_POST['patient_religion'];
    $patient_address = $_POST['patient_address'];
    $patient_sitio = $_POST['patient_sitio'];
    $patient_barangay = $_POST['patient_barangay'];
    $patient_complete_address = $_POST['patient_complete_address']; // New field

    $client_name = $_POST['client_name'];
    $client_age = $_POST['client_age'];
    $client_gender = $_POST['client_gender'];
    $client_civil_status = $_POST['client_civil_status'];
    $client_birthday = $_POST['client_birthday'];
    $client_birthplace = $_POST['client_birthplace'];
    $client_education = $_POST['client_education'];
    $client_occupation = $_POST['client_occupation'];
    $client_religion = $_POST['client_religion'];
    $client_address = $_POST['client_address'];
    $client_sitio = $_POST['client_sitio'];
    $client_barangay = $_POST['client_barangay'];
    $client_complete_address = $_POST['client_complete_address']; // New field
    
    $request_type = $_POST['request_type'];
    $request_date = $_POST['request_date'];
    $amount = $_POST['amount'];
    $diagnosis_school = $_POST['diagnosis_school'];
    $prep_by = $_POST['prep_by'];
    $pos_prep = $_POST['pos_prep'];
    $not_by = $_POST['not_by'];
    $pos_not = $_POST['pos_not'];
    $id_type = $_POST['id_type'];

    // SQL Insert Query
    $sql = "INSERT INTO transactions (
                beneficiary_id, 
                patient_name, patient_age, relation, patient_gender, patient_civil_status, 
                patient_birthday, patient_birthplace, patient_education, patient_occupation, 
                patient_religion, patient_sitio, patient_barangay, patient_address,
                patient_complete_address,  -- Added field
                client_name, client_age, client_gender, client_civil_status, client_birthday, 
                client_birthplace, client_education, client_occupation, client_religion, 
                client_sitio, client_barangay, client_address,
                client_complete_address,  -- Added field
                request_type, request_purpose, request_date, amount, diagnosis_school, 
                prep_by, pos_prep, not_by, pos_not, id_type
            ) VALUES (
                :beneficiary_id, 
                :patient_name, :patient_age, :relation, :patient_gender, :patient_civil_status,
                :patient_birthday, :patient_birthplace, :patient_education, :patient_occupation,
                :patient_religion, :patient_sitio, :patient_barangay, :patient_address,
                :patient_complete_address,  -- Added parameter
                :client_name, :client_age, :client_gender, :client_civil_status, :client_birthday,
                :client_birthplace, :client_education, :client_occupation, :client_religion,
                :client_sitio, :client_barangay, :client_address,
                :client_complete_address,  -- Added parameter
                :request_type, :request_purpose, :request_date, :amount, :diagnosis_school,
                :prep_by, :pos_prep, :not_by, :pos_not, :id_type
            )";

    $stmt = $conn->prepare($sql);

    // Bind parameters and execute query
    $stmt->execute([
        ':beneficiary_id' => $beneficiary_id,
        ':patient_name' => $patient_name,
        ':patient_age' => $patient_age,
        ':relation' => $relation,
        ':patient_gender' => $patient_gender,
        ':patient_civil_status' => $patient_civil_status,
        ':patient_birthday' => $patient_birthday,
        ':patient_birthplace' => $patient_birthplace,
        ':patient_education' => $patient_education,
        ':patient_occupation' => $patient_occupation,
        ':patient_religion' => $patient_religion,
        ':patient_sitio' => $patient_sitio,
        ':patient_barangay' => $patient_barangay,
        ':patient_address' => $patient_address,
        ':patient_complete_address' => $patient_complete_address,
        ':client_name' => $client_name,
        ':client_age' => $client_age,
        ':client_gender' => $client_gender,
        ':client_civil_status' => $client_civil_status,
        ':client_birthday' => $client_birthday,
        ':client_birthplace' => $client_birthplace,
        ':client_education' => $client_education,
        ':client_occupation' => $client_occupation,
        ':client_religion' => $client_religion,
        ':client_sitio' => $client_sitio,
        ':client_barangay' => $client_barangay,
        ':client_address' => $client_address,
        ':client_complete_address' => $client_complete_address,
        ':request_type' => $request_type,
        ':request_purpose' => $formatted_purpose,
        ':request_date' => $request_date,
        ':amount' => $amount,
        ':diagnosis_school' => $diagnosis_school,
        ':prep_by' => $prep_by,
        ':pos_prep' => $pos_prep,
        ':not_by' => $not_by,
        ':pos_not' => $pos_not,
        ':id_type' => $id_type
    ]);

    $transaction_id = $conn->lastInsertId();

    // Add this after getting the transaction_id and before updating the balance
    try {
        // Get default amount from allocation_settings
        $settings_stmt = $conn->prepare("SELECT default_amount, validity_months FROM allocation_settings LIMIT 1");
        $settings_stmt->execute();
        $settings = $settings_stmt->fetch(PDO::FETCH_ASSOC);

        // Check if beneficiary already has a money status record
        $check_stmt = $conn->prepare("SELECT id FROM beneficiary_money_status WHERE beneficiary_id = ?");
        $check_stmt->execute([$beneficiary_id]);
        $exists = $check_stmt->fetch();

        if (!$exists) {
            // First transaction - create new record
            $bms_sql = "INSERT INTO beneficiary_money_status 
                        (beneficiary_id, remaining_money, first_transaction_date, last_transaction_date) 
                        VALUES 
                        (:beneficiary_id, :remaining_money, NOW(), NOW())";
            
            $bms_stmt = $conn->prepare($bms_sql);
            $bms_stmt->execute([
                ':beneficiary_id' => $beneficiary_id,
                ':remaining_money' => $settings['default_amount'] - $amount
            ]);
        } else {
            // Update existing record
            $update_sql = "UPDATE beneficiary_money_status 
                          SET remaining_money = remaining_money - :amount,
                              last_transaction_date = NOW()
                          WHERE beneficiary_id = :beneficiary_id";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->execute([
                ':amount' => $amount,
                ':beneficiary_id' => $beneficiary_id
            ]);
        }

        // Remove the existing balance update code since we're handling it in beneficiary_money_status now
        // Remove or comment out these lines:
        // $update_balance = "UPDATE beneficiary_alloted_money...
        // $balance_stmt = $conn->prepare($update_balance);...

    } catch (PDOException $e) {
        $conn->rollBack();
        throw $e; // Re-throw to be caught by outer try-catch
    }

    $log_sql = "INSERT INTO activity_logs (act_name, act_type) VALUES (:act_name, :act_type)";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->execute([
        ':act_name' => "Transaction recorded for " . $patient_name,
        ':act_type' => "Financial Assistance"
    ]);

    // Reset requirements status
    $service_sql = "SELECT id FROM services WHERE name = :purpose";
    $service_stmt = $conn->prepare($service_sql);
    $service_stmt->execute([':purpose' => $formatted_purpose]);
    $service_id = $service_stmt->fetchColumn();

    // Delete existing requirements status
    $delete_status = "DELETE brs FROM beneficiary_requirements_status brs
                      INNER JOIN requirements r ON brs.requirement_id = r.id
                      WHERE brs.beneficiary_id = :beneficiary_id
                      AND r.service_id = :service_id";

    $del_stat_stmt = $conn->prepare($delete_status);
    $del_stat_stmt->execute([
        ':beneficiary_id' => $beneficiary_id,
        ':service_id' => $service_id
    ]);

    // Delete beneficiary requirements
    $delete_requirements = "DELETE br FROM beneficiary_requirements br
                           INNER JOIN requirements r ON br.requirement_id = r.id
                           WHERE br.beneficiary_id = :beneficiary_id
                           AND r.service_id = :service_id";

    $del_req_stmt = $conn->prepare($delete_requirements);
    $del_req_stmt->execute([
        ':beneficiary_id' => $beneficiary_id,
        ':service_id' => $service_id
    ]);

    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction recorded successfully',
        'transaction_id' => $transaction_id
    ]);

} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>