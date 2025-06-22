<?php
include '../includes/db.php';

// Add this near the top after including db.php
if (isset($_GET['error'])) {
    $error = $_GET['error'];
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($error) . "</div>";
}

// Get ID from query param
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$beneficiary = null;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM beneficiaries WHERE id = ?");
    $stmt->execute([$id]);
    $beneficiary = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$beneficiary) {
        die("Beneficiary not found.");
    }
} else {
    die("Invalid beneficiary ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Beneficiary</title>
    <link rel="stylesheet" href="../assets/css/beneficiary.css" />
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1>Edit Beneficiary</h1>
            
            <form class="beneficiary-form" id="beneficiaryForm" method="POST" action="update-beneficiary.php" onsubmit="return validateForm()">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="fullName" class="form-label">Full Name:</label>
                        <input type="text" id="fullName" name="fullName" class="form-input" value="<?= htmlspecialchars($beneficiary['full_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="birthday" class="form-label">Birthday:</label>
                        <input type="date" id="birthday" name="birthday" class="form-input" value="<?= htmlspecialchars($beneficiary['birthday']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="age" class="form-label">Age:</label>
                        <input type="number" id="age" name="age" class="form-input" value="<?= htmlspecialchars($beneficiary['age']) ?>" min="0" max="120" required>
                    </div>

                    <div class="form-group">
                        <label for="gender" class="form-label">Gender/Sex:</label>
                        <select id="gender" name="gender" class="form-select" required>
                            <option value="">-- Select Gender --</option>
                            <option value="Male" <?= $beneficiary['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $beneficiary['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="civilStatus" class="form-label">Civil Status:</label>
                        <select id="civilStatus" name="civilStatus" class="form-select" required>
                            <?php
                            $statuses = ['Single', 'Married', 'Widowed', 'Separated', 'Divorced'];
                            foreach ($statuses as $status) {
                                $selected = ($beneficiary['civil_status'] == $status) ? 'selected' : '';
                                echo "<option value=\"$status\" $selected>$status</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="birthplace" class="form-label">Birthplace:</label>
                        <input type="text" id="birthplace" name="birthplace" class="form-input" value="<?= htmlspecialchars($beneficiary['birthplace']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="education" class="form-label">Educational Attainment:</label>
                        <input type="text" id="education" name="education" class="form-input" value="<?= htmlspecialchars($beneficiary['education']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="occupation" class="form-label">Occupation:</label>
                        <input type="text" id="occupation" name="occupation" class="form-input" value="<?= htmlspecialchars($beneficiary['occupation']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="religion" class="form-label">Religion:</label>
                        <input type="text" id="religion" name="religion" class="form-input" value="<?= htmlspecialchars($beneficiary['religion']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="barangay" class="form-label">Barangay:</label>
                        <input type="text" id="barangay" name="barangay" class="form-input" value="<?= htmlspecialchars($beneficiary['barangay']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="sitio" class="form-label">Sitio/Purok:</label>
                        <input type="text" id="sitio" name="sitio" class="form-input" value="<?= htmlspecialchars($beneficiary['sitio']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="dateAdded" class="form-label">Date Added:</label>
                        <input type="date" id="dateAdded" name="dateAdded" class="form-input" value="<?= htmlspecialchars($beneficiary['date_added']) ?>" readonly>
                    </div>
                </div>

                <!-- Hidden field for edit mode -->
                <input type="hidden" id="beneficiaryId" name="beneficiaryId" value="<?= $beneficiary['id'] ?>">
                <input type="hidden" id="action" name="action" value="edit">

                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="view.php?id=<?= $beneficiary['id'] ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </main>

    <script src="../assets/js/beneficiary.js"></script>
    <script>
        // Auto load sitios for current barangay on page load
        document.addEventListener("DOMContentLoaded", function() {
            loadSitios(document.getElementById('barangay').value);
        });
    </script>
</body>
</html>
