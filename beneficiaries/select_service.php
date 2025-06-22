<?php
require_once '../includes/db.php';

$beneficiary_id = isset($_GET['beneficiary_id']) ? (int)$_GET['beneficiary_id'] : 0;

// Fetch beneficiary details
$stmt = $pdo->prepare("SELECT full_name FROM beneficiaries WHERE id = ?");
$stmt->execute([$beneficiary_id]);
$beneficiary = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch available services
$stmt = $pdo->query("SELECT id, name, description FROM services");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Service</title>
    <link rel="stylesheet" href="../assets/css/select_service.css">
</head>
<body>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <a href="view.php?id=<?= $beneficiary_id ?>" class="back-button">
                    ← Back to Beneficiary
                </a>
                <h1>Select Service to Apply</h1>
                <p>Beneficiary: <?= htmlspecialchars($beneficiary['full_name']) ?></p>
            </div>

            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                    <div class="service-card">
                        <h3><?= htmlspecialchars($service['name']) ?></h3>
                        <p><?= htmlspecialchars($service['description']) ?></p>
                        <button onclick="checkRequirements(<?= $beneficiary_id ?>, <?= $service['id'] ?>)" 
                                class="btn btn-primary">
                            Select Service
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <script>
    async function checkRequirements(beneficiaryId, serviceId) {
        try {
            const response = await fetch(`check_requirements.php?beneficiary_id=${beneficiaryId}&service_id=${serviceId}`);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Error checking requirements');
            }
            
            if (data.complete) {
                window.location.href = `../documents/index.php?id=${beneficiaryId}&purpose=${serviceId}`;
            } else {
                alert('Please complete all requirements for this service first!\n\nMissing requirements:\n- ' + 
                      data.missing.join('\n- '));
                window.location.href = `requirements.php?beneficiary_id=${beneficiaryId}&service_id=${serviceId}`;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error checking requirements: ' + error.message);
        }
    }
    </script>
</body>
</html>