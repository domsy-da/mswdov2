<?php

require_once '../includes/db.php';

$beneficiary_id = isset($_GET['beneficiary_id']) ? (int)$_GET['beneficiary_id'] : 0;

// Fetch services
$stmt = $pdo->query("SELECT * FROM services");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch beneficiary's submitted requirements
$stmt = $pdo->prepare("SELECT requirement_id, is_submitted FROM beneficiary_requirements WHERE beneficiary_id = ?");
$stmt->execute([$beneficiary_id]);
$submitted_requirements = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Requirements Checklist</title>
    <link rel="stylesheet" href="../assets/css/requirements.css">
</head>
<body>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <a href="view.php?id=<?= $beneficiary_id ?>" class="back-button">← Back to Beneficiary</a>
                <h1>Requirements Checklist</h1>
            </div>

            <div class="requirements-form">
                <div class="form-group">
                    <label for="service">Select Service Type:</label>
                    <select id="service" onchange="loadRequirements(this.value)">
                        <option value="">Select a service...</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="requirements-list" class="requirements-list">
                    <!-- Requirements will be loaded here dynamically -->
                </div>

                <div class="form-actions">
                    <button onclick="saveRequirements()" class="btn btn-primary" id="saveBtn" style="display: none;">
                        Save Requirements
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
    let currentRequirements = [];

    async function loadRequirements(serviceId) {
        if (!serviceId) {
            document.getElementById('requirements-list').innerHTML = '';
            document.getElementById('saveBtn').style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`get_requirements.php?service_id=${serviceId}&beneficiary_id=<?= $beneficiary_id ?>`);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message || 'Error loading requirements');
            
            currentRequirements = data.requirements;
            
            const container = document.getElementById('requirements-list');
            if (data.requirements.length === 0) {
                container.innerHTML = '<div class="no-requirements">No requirements found for this service.</div>';
                document.getElementById('saveBtn').style.display = 'none';
                return;
            }

            container.innerHTML = data.requirements.map(req => `
                <div class="requirement-item">
                    <label>
                        <input type="checkbox" 
                               data-requirement-id="${req.id}"
                               ${req.is_submitted ? 'checked' : ''}>
                        ${req.requirement_name}
                    </label>
                </div>
            `).join('');
            
            document.getElementById('saveBtn').style.display = 'block';
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('requirements-list').innerHTML = 
                '<div class="error-message">Error loading requirements. Please try again.</div>';
            document.getElementById('saveBtn').style.display = 'none';
        }
    }

    async function saveRequirements() {
        try {
            const checkboxes = document.querySelectorAll('#requirements-list input[type="checkbox"]');
            const requirements = Array.from(checkboxes).map(checkbox => ({
                requirement_id: checkbox.dataset.requirementId,
                is_submitted: checkbox.checked
            }));

            const response = await fetch('update_requirement.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    beneficiary_id: <?= $beneficiary_id ?>,
                    requirements: requirements
                })
            });

            const data = await response.json();
            if (data.success) {
                alert('Requirements saved successfully!');
            } else {
                throw new Error(data.message || 'Failed to save requirements');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error saving requirements: ' + error.message);
        }
    }
    </script>
</body>
</html>