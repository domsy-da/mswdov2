<?php
// Include DB connection
include '../includes/db.php'; // adjust path to your db.php

header('Content-Type: application/json');

// Basic validation
$requiredFields = [
    'fullName', 'birthday', 'age', 'gender', 'civilStatus', 'birthplace',
    'education', 'occupation', 'religion', 'barangay', 'sitio', 'dateAdded'
];

foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode([
            'success' => false,
            'message' => "Missing required field: $field"
        ]);
        exit;
    }
}

// Prepare data
$fullName = $_POST['fullName'];
$birthday = $_POST['birthday'];
$age = $_POST['age'];
$gender = $_POST['gender'];
$civilStatus = $_POST['civilStatus'];
$birthplace = $_POST['birthplace'];
$education = $_POST['education'];
$occupation = $_POST['occupation'];
$religion = $_POST['religion'];
$barangay = $_POST['barangay'];
$sitio = $_POST['sitio'];
$dateAdded = $_POST['dateAdded'];

// Insert into DB
try {
    $stmt = $pdo->prepare("INSERT INTO beneficiaries 
        (full_name, birthday, age, gender, civil_status, birthplace, education, occupation, religion, barangay, sitio, date_added)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $fullName, $birthday, $age, $gender, $civilStatus, $birthplace, $education,
        $occupation, $religion, $barangay, $sitio, $dateAdded
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
