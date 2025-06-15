<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $data = [
            'full_name' => $_POST['fullName'],
            'birthday' => $_POST['birthday'],
            'age' => $_POST['age'],
            'gender' => $_POST['gender'],
            'civil_status' => $_POST['civilStatus'],
            'birthplace' => $_POST['birthplace'],
            'education' => $_POST['education'],
            'occupation' => $_POST['occupation'],
            'religion' => $_POST['religion'],
            'barangay' => $_POST['barangay_name'], // Using the name from hidden input
            'sitio' => $_POST['sitio_name'], // Using the name from hidden input
            'date_added' => date('Y-m-d')
        ];

        // Insert into database
        $sql = "INSERT INTO beneficiaries (
                    full_name, birthday, age, gender, civil_status, 
                    birthplace, education, occupation, religion, 
                    barangay, sitio, date_added
                ) VALUES (
                    :full_name, :birthday, :age, :gender, :civil_status,
                    :birthplace, :education, :occupation, :religion,
                    :barangay, :sitio, :date_added
                )";

        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute($data)) {
            // Redirect with success message
            header('Location: index.php?message=success&action=add');
            exit;
        } else {
            throw new Exception("Error saving beneficiary");
        }

    } catch (Exception $e) {
        // Redirect with error message
        header('Location: add.php?error=' . urlencode($e->getMessage()));
        exit;
    }
} else {
    // If not POST request, redirect to index
    header('Location: index.php');
    exit;
}