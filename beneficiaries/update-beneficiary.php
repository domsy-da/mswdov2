<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $beneficiaryId = (int)$_POST['beneficiaryId'];
        $data = [
            'full_name' => $_POST['fullName'],
            'birthday' => $_POST['birthday'],
            'age' => (int)$_POST['age'],
            'gender' => $_POST['gender'],
            'civil_status' => $_POST['civilStatus'],
            'birthplace' => $_POST['birthplace'],
            'education' => $_POST['education'],
            'occupation' => $_POST['occupation'],
            'religion' => $_POST['religion'],
            'barangay' => $_POST['barangay'],
            'sitio' => $_POST['sitio']
        ];

        // Update query
        $sql = "UPDATE beneficiaries SET 
                full_name = :full_name,
                birthday = :birthday,
                age = :age,
                gender = :gender,
                civil_status = :civil_status,
                birthplace = :birthplace,
                education = :education,
                occupation = :occupation,
                religion = :religion,
                barangay = :barangay,
                sitio = :sitio
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $beneficiaryId, PDO::PARAM_INT);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        if ($stmt->execute()) {
            // Redirect back to view page with success message
            header("Location: view.php?id=$beneficiaryId&message=updated");
            exit;
        } else {
            throw new Exception("Error updating beneficiary");
        }

    } catch (Exception $e) {
        // Redirect back to edit page with error
        header("Location: edit.php?id=$beneficiaryId&error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // If not POST request, redirect to index
    header("Location: index.php");
    exit;
}