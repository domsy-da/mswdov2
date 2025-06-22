<?php
require_once '../includes/db.php';

// Fetch barangays for dropdown
$stmt = $pdo->query("SELECT id, name FROM barangays ORDER BY name ASC");
$barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Beneficiary - MSWDO Management System</title>
    <style>
        :root {
            --primary-color: #1a1a1a;
            --secondary-color: #4a4a4a;
            --border-color: #e5e5e5;
            --background-light: #ffffff;
            --background-dark: #f5f5f5;
            --text-primary: #2a2a2a;
            --text-secondary: #666666;
            --error-color: #dc3545;
            --success-color: #28a745;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            background-color: var(--background-dark);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
        }

        .main-content {
            padding: 2rem;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: var(--background-light);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }

        .page-header {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            color: var(--secondary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            color: var(--primary-color);
            transform: translateX(-3px);
        }

        .back-arrow {
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }

        h1 {
            color: var(--primary-color);
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            color: var(--text-primary);
            background-color: var(--background-light);
            transition: all 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(26, 26, 26, 0.1);
        }

        .form-input:hover,
        .form-select:hover {
            border-color: var(--secondary-color);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }

        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 120px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #000000;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: var(--background-dark);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background-color: #e8e8e8;
        }

        .form-input.is-invalid,
        .form-select.is-invalid {
            border-color: var(--error-color);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <a href="index.php" class="back-button">
                    <span class="back-arrow">←</span> Back to List
                </a>
                <h1>Add New Beneficiary</h1>
            </div>

            <form class="beneficiary-form" id="beneficiaryForm" method="POST" action="process_beneficiary.php">
                <div class="form-grid">
                    <!-- Personal Information -->
                    <div class="form-group">
                        <label for="fullName" class="form-label">Full Name:</label>
                        <input type="text" id="fullName" name="fullName" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="birthday" class="form-label">Birthday:</label>
                        <input type="date" id="birthday" name="birthday" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="age" class="form-label">Age:</label>
                        <input type="number" id="age" name="age" class="form-input" readonly required>
                    </div>

                    <div class="form-group">
                        <label for="gender" class="form-label">Gender:</label>
                        <select id="gender" name="gender" class="form-select" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="civilStatus" class="form-label">Civil Status:</label>
                        <select id="civilStatus" name="civilStatus" class="form-select" required>
                            <option value="">Select Civil Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="barangay" class="form-label">Barangay:</label>
                        <select id="barangay" name="barangay" class="form-select" required onchange="loadSitios(this.value)">
                            <option value="">Select a Barangay</option>
                            <?php foreach ($barangays as $barangay): ?>
                                <option value="<?= htmlspecialchars($barangay['id']) ?>" 
                                        data-name="<?= htmlspecialchars($barangay['name']) ?>">
                                    <?= htmlspecialchars($barangay['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sitio" class="form-label">Sitio/Purok:</label>
                        <select id="sitio" name="sitio" class="form-select" required>
                            <option value="">Select Sitio/Purok</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="birthplace" class="form-label">Birthplace:</label>
                        <input type="text" id="birthplace" name="birthplace" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="education" class="form-label">Educational Attainment:</label>
                        <input type="text" id="education" name="education" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="occupation" class="form-label">Occupation:</label>
                        <input type="text" id="occupation" name="occupation" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="religion" class="form-label">Religion:</label>
                        <input type="text" id="religion" name="religion" class="form-input" required>
                    </div>

                    <!-- Hidden inputs for names -->
                    <input type="hidden" name="barangay_name" id="barangay_name">
                    <input type="hidden" name="sitio_name" id="sitio_name">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Beneficiary</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script src="js/add-beneficiary.js"></script>
    <script>
// Calculate age from birthday
document.getElementById('birthday').addEventListener('change', function() {
    const birthdayDate = new Date(this.value);
    const today = new Date();
    let age = today.getFullYear() - birthdayDate.getFullYear();
    const monthDiff = today.getMonth() - birthdayDate.getMonth();
    
    // Adjust age if birthday hasn't occurred this year
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdayDate.getDate())) {
        age--;
    }
    
    document.getElementById('age').value = age;
});

// Load sitios based on selected barangay
async function loadSitios(barangayId) {
    if (!barangayId) {
        document.getElementById('sitio').innerHTML = '<option value="">Select Sitio/Purok</option>';
        document.getElementById('barangay_name').value = '';
        return;
    }

    // Set the barangay name in hidden input
    const selectedBarangay = document.querySelector(`#barangay option[value="${barangayId}"]`);
    document.getElementById('barangay_name').value = selectedBarangay.dataset.name;

    try {
        const response = await fetch(`../api/get_sitios.php?barangay_id=${encodeURIComponent(barangayId)}`);
        if (!response.ok) throw new Error('Failed to fetch sitios');
        
        const sitios = await response.json();
        const sitioSelect = document.getElementById('sitio');
        
        // Clear and add default option
        sitioSelect.innerHTML = '<option value="">Select Sitio/Purok</option>';
        
        // Add sitios to dropdown
        sitios.forEach(sitio => {
            const option = document.createElement('option');
            option.value = sitio.id;
            option.textContent = sitio.name;
            option.dataset.name = sitio.name;
            sitioSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading sitios:', error);
        alert('Error loading sitios. Please try again.');
    }
}

// Make age input readonly since it's calculated
document.getElementById('age').readOnly = true;

// Add event listener for sitio selection to store name
document.getElementById('sitio').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        this.dataset.selectedName = selectedOption.textContent;
    }
});

// Update form submission to use names instead of IDs
document.getElementById('beneficiaryForm').addEventListener('submit', function(e) {
    const sitioSelect = document.getElementById('sitio');
    const selectedSitio = sitioSelect.options[sitioSelect.selectedIndex];
    if (selectedSitio.value) {
        // Create hidden input for sitio name if it doesn't exist
        let sitioNameInput = document.getElementById('sitio_name');
        if (!sitioNameInput) {
            sitioNameInput = document.createElement('input');
            sitioNameInput.type = 'hidden';
            sitioNameInput.name = 'sitio_name';
            sitioNameInput.id = 'sitio_name';
            this.appendChild(sitioNameInput);
        }
        sitioNameInput.value = selectedSitio.textContent;
    }
});
</script>
</body>
</html>