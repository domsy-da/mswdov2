<?php
require_once '../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No data received']);
    exit;
}

// Map request_purpose
if ($data['request_purpose'] == 'educational') {
    $request_purpose = 'Educational';
} elseif ($data['request_purpose'] == 'med_exp') {
    $request_purpose = 'Medical Expense';
} elseif ($data['request_purpose'] == 'burial') {
    $request_purpose = 'Burial';
} else {
    $request_purpose = 'Other';
}

// Prepare the data array
$params = [
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
    'client_civil_status' => $data['civil_status'] ?? null,
    'client_birthday' => $data['birthday'] ?? null,
    'client_birthplace' => $data['birthplace'] ?? null,
    'client_education' => $data['educational'] ?? null,
    'client_occupation' => $data['occupation'] ?? null,
    'client_religion' => $data['religion'] ?? null,
    'client_sitio' => $data['sitio'] ?? null,
    'client_barangay' => $data['barangay'] ?? null,
    'client_complete_address' => $data['complete_address'] ?? null,
    'request_type' => $data['request_type'] ?? null,
    'request_purpose' => $request_purpose,
    'request_date' => $data['request_date'] ?? null,
    'amount' => $data['amount'] ?? $data['request_amount'] ?? null,
    'diagnosis_school' => $data['diagnosis_school'] ?? $data['request_diagnosis'] ?? null,
    'prep_by' => $data['prep_by'] ?? null,
    'pos_prep' => $data['pos_prep'] ?? null,
    'not_by' => $data['not_by'] ?? null,
    'pos_not' => $data['pos_not'] ?? null,
    'id_type' => $data['id_type'] ?? null
];

try {
    // Check if a record exists for this beneficiary_id
    $check = $pdo->prepare("SELECT id FROM temp_tran WHERE beneficiary_id = ?");
    $check->execute([$params['beneficiary_id']]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update
        $update = $pdo->prepare("
            UPDATE temp_tran SET
                patient_name = :patient_name,
                patient_age = :patient_age,
                relation = :relation,
                patient_gender = :patient_gender,
                patient_civil_status = :patient_civil_status,
                patient_birthday = :patient_birthday,
                patient_birthplace = :patient_birthplace,
                patient_education = :patient_education,
                patient_occupation = :patient_occupation,
                patient_religion = :patient_religion,
                patient_sitio = :patient_sitio,
                patient_barangay = :patient_barangay,
                patient_complete_address = :patient_complete_address,
                client_name = :client_name,
                client_age = :client_age,
                client_gender = :client_gender,
                client_civil_status = :client_civil_status,
                client_birthday = :client_birthday,
                client_birthplace = :client_birthplace,
                client_education = :client_education,
                client_occupation = :client_occupation,
                client_religion = :client_religion,
                client_sitio = :client_sitio,
                client_barangay = :client_barangay,
                client_complete_address = :client_complete_address,
                request_type = :request_type,
                request_purpose = :request_purpose,
                request_date = :request_date,
                amount = :amount,
                diagnosis_school = :diagnosis_school,
                prep_by = :prep_by,
                pos_prep = :pos_prep,
                not_by = :not_by,
                pos_not = :pos_not,
                id_type = :id_type,
                updated_at = NOW()
            WHERE beneficiary_id = :beneficiary_id
        ");
        $update->execute($params);
        $id = $existing['id'];
    } else {
        // Insert
        $insert = $pdo->prepare("
            INSERT INTO temp_tran (
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
            )
        ");
        $insert->execute($params);
        $id = $pdo->lastInsertId();
    }

    echo json_encode([
        'success' => true,
        'id' => $id
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}