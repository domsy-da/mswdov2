<?php
require_once '../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No data received']);
    exit;
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
        'beneficiary_id' => $data['beneficiary_id'] ?? null,
        'patient_name' => $data['patient_name'] ?? null,
        'patient_age' => $data['patient_age'] ?? null,
        'relation' => $data['relation'] ?? null,
        'patient_gender' => $data['patient_gender'] ?? null,
        'patient_civil_status' => $data['patient_civil_status'] ?? null,
        'patient_birthday' => $data['patient_birthday'] ?? null,
        'patient_birthplace' => $data['patient_birthplace'] ?? null,
        'patient_education' => $data['patient_education'] ?? null,
        'patient_occupation' => $data['patient_occupation'] ?? null,
        'patient_religion' => $data['patient_religion'] ?? null,
        'patient_sitio' => $data['patient_sitio'] ?? null,
        'patient_barangay' => $data['patient_barangay'] ?? null,
        'patient_complete_address' => $data['patient_complete_address'] ?? null,
        'client_name' => $data['client_name'] ?? null,
        'client_age' => $data['client_age'] ?? null,
        'client_gender' => $data['client_gender'] ?? null,
        'client_civil_status' => $data['client_civil_status'] ?? null,
        'client_birthday' => $data['client_birthday'] ?? null,
        'client_birthplace' => $data['client_birthplace'] ?? null,
        'client_education' => $data['client_education'] ?? null,
        'client_occupation' => $data['client_occupation'] ?? null,
        'client_religion' => $data['client_religion'] ?? null,
        'client_sitio' => $data['client_sitio'] ?? null,
        'client_barangay' => $data['client_barangay'] ?? null,
        'client_complete_address' => $data['client_complete_address'] ?? null,
        'request_type' => $data['request_type'] ?? null,
        'request_purpose' => $data['request_purpose'] ?? null,
        'request_date' => $data['request_date'] ?? null,
        'amount' => $data['amount'] ?? $data['request_amount'] ?? null,
        'diagnosis_school' => $data['diagnosis_school'] ?? $data['request_diagnosis'] ?? null,
        'prep_by' => $data['prep_by'] ?? null,
        'pos_prep' => $data['pos_prep'] ?? null,
        'not_by' => $data['not_by'] ?? null,
        'pos_not' => $data['pos_not'] ?? null,
        'id_type' => $data['id_type'] ?? null
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