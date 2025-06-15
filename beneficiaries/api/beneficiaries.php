<?php
// api/beneficiaries.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database connection (update with your real db config)
$host = 'localhost';
$dbname = 'mswdo_dbv2';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Build base query
$sql = "SELECT id, full_name, age, gender, barangay, occupation, date_added FROM beneficiaries WHERE 1=1";

// Filters (optional)
$params = [];

// Search filter
if (!empty($_GET['search'])) {
    $sql .= " AND full_name LIKE :search";
    $params[':search'] = '%' . $_GET['search'] . '%';
}

// Gender filter
if (!empty($_GET['gender'])) {
    $sql .= " AND gender = :gender";
    $params[':gender'] = $_GET['gender'];
}

// Barangay filter
if (!empty($_GET['barangay'])) {
    $sql .= " AND barangay = :barangay";
    $params[':barangay'] = $_GET['barangay'];
}

// Execute query
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $beneficiaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON
    echo json_encode($beneficiaries);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch beneficiaries: ' . $e->getMessage()]);
    exit;
}
