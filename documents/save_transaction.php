<?php
include 'config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
if ($data['request_purpose'] == 'educational') {
    $request_purpose = 'Educational';
} elseif ($data['request_purpose'] == 'med_exp') {
    $request_purpose = 'Medical Expense';
} elseif ($data['request_purpose'] == 'burial') {
    $request_purpose = 'Burial';
} else {
    $request_purpose = 'Other';
}

try {
    $sql = "INSERT INTO transactions (
        beneficiary_id, patient_name, patient_age, relation, patient_gender,
        patient_civil_status, patient_birthday, patient_birthplace, patient_education,
        patient_occupation, patient_religion, patient_sitio, patient_barangay,
        patient_complete_address, client_name, client_age, client_gender,
        client_civil_status, client_birthday, client_birthplace, client_education,
        client_occupation, client_religion, client_sitio, client_barangay,
        client_complete_address, request_type, request_purpose, request_date,
        amount, diagnosis_school, prep_by, pos_prep, not_by, pos_not, id_type,
        created_at, updated_at
    ) VALUES (
        :beneficiary_id, :patient_name, :patient_age, :relation, :patient_gender,
        :patient_civil_status, :patient_birthday, :patient_birthplace, :patient_education,
        :patient_occupation, :patient_religion, :patient_sitio, :patient_barangay,
        :patient_complete_address, :client_name, :client_age, :client_gender,
        :client_civil_status, :client_birthday, :client_birthplace, :client_education,
        :client_occupation, :client_religion, :client_sitio, :client_barangay,
        :client_complete_address, :request_type, :request_purpose, :request_date,
        :amount, :diagnosis_school, :prep_by, :pos_prep, :not_by, :pos_not, :id_type,
        NOW(), NOW()
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'beneficiary_id' => $data['beneficiary_id'],
        'patient_name' => $data['patient_name'],
        'patient_age' => $data['patient_age'],
        'relation' => $data['relation'],
        'patient_gender' => $data['patient_gender'],
        'patient_civil_status' => $data['patient_civil_status'],
        'patient_birthday' => $data['patient_birthday'],
        'patient_birthplace' => $data['patient_birthplace'],
        'patient_education' => $data['patient_education'],
        'patient_occupation' => $data['patient_occupation'],
        'patient_religion' => $data['patient_religion'],
        'patient_sitio' => $data['patient_sitio'],
        'patient_barangay' => $data['patient_barangay'],
        'patient_complete_address' => $data['patient_complete_address'],
        'client_name' => $data['client_name'],
        'client_age' => $data['client_age'],
        'client_gender' => $data['client_gender'],
        'client_civil_status' => $data['civil_status'],
        'client_birthday' => $data['birthday'],
        'client_birthplace' => $data['birthplace'],
        'client_education' => $data['educational'],
        'client_occupation' => $data['occupation'],
        'client_religion' => $data['religion'],
        'client_sitio' => $data['sitio'],
        'client_barangay' => $data['barangay'],
        'client_complete_address' => $data['complete_address'],
        'request_type' => $data['request_type'],
        'request_purpose' => $request_purpose,
        'request_date' => $data['request_date'],
        'amount' => $data['request_amount'],
        'diagnosis_school' => $data['request_diagnosis'],
        'prep_by' => $data['prep_by'],
        'pos_prep' => $data['pos_prep'],
        'not_by' => $data['not_by'],
        'pos_not' => $data['pos_not'],
        'id_type' => $data['id_type']
    ]);

    $transaction_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'transaction_id' => $transaction_id
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}