<?php
include '../includes/db.php';

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

// Query for relatives information
$relatives_stmt = $pdo->prepare("SELECT name, age, civil_status, relationship, educational_attainment, occupation 
                                FROM relatives 
                                WHERE beneficiary_id = ?");
$relatives_stmt->execute([$id]);
$relatives = $relatives_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get money status and settings
$money_status = null;
$allocation_settings = null;

// Get allocation settings
$settings_stmt = $pdo->query("SELECT * FROM allocation_settings LIMIT 1");
$allocation_settings = $settings_stmt->fetch(PDO::FETCH_ASSOC);

// Get money status with latest transaction
$status_stmt = $pdo->prepare("
    SELECT bms.*, 
           COALESCE(SUM(t.amount), 0) as total_spent
    FROM beneficiary_money_status bms
    LEFT JOIN transactions t ON bms.beneficiary_id = t.beneficiary_id
    WHERE bms.beneficiary_id = ?
    GROUP BY bms.beneficiary_id, bms.remaining_money, bms.first_transaction_date, bms.last_transaction_date
");
$status_stmt->execute([$id]);
$money_status = $status_stmt->fetch(PDO::FETCH_ASSOC);

// Calculate validity info and remaining balance
$validity_info = '';
$remaining_balance = 0;

if ($money_status) {
    $remaining_balance = $money_status['remaining_money'];

    if ($money_status['last_transaction_date']) {
        // Use last transaction date for validity calculation
        $start = new DateTime($money_status['last_transaction_date']);
        $end = clone $start;
        $end->modify("+{$allocation_settings['validity_months']} months");
        $now = new DateTime();

        if ($now > $end) {
            $validity_info = "Expired";
        } else {
            $interval = $end->diff($now);
            $months = $interval->m;
            $weeks = floor($interval->d / 7);
            $remaining_days = $interval->d % 7;

            $validity_parts = [];
            if ($months > 0) {
                $validity_parts[] = "{$months} months";
            }
            if ($weeks > 0) {
                $validity_parts[] = "{$weeks} weeks";
            }
            if ($remaining_days > 0) {
                $validity_parts[] = "{$remaining_days} days";
            }

            $validity_info = "Valid for " . implode(', ', $validity_parts);
        }
    }
} else {
    $remaining_balance = $allocation_settings['default_amount'];
    $validity_info = "{$allocation_settings['validity_months']} months validity when activated";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Beneficiary - MSWDO Management System</title>
    <link rel="stylesheet" href="../assets/css/beneficiary.css" />
    <link rel="stylesheet" href="view.css" />
    <style>
        .beneficiary-id {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #666;
            font-size: 0.9rem;
            flex-wrap: wrap;
        }

        .money-status {
            color: #333;
            font-weight: 600;
        }

        .validity-status {
            color: #666;
            font-style: italic;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-active {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Beneficiary Details</h1>
                <p class="page-subtitle">Complete information and profile overview</p>
            </div>

            <!-- Beneficiary Card -->
            <div class="beneficiary-card">
                <div class="card-header">
                    <h2 class="beneficiary-name"><?= htmlspecialchars($beneficiary['full_name']) ?></h2>
                    <p class="beneficiary-id">
                        ID: <?= htmlspecialchars($beneficiary['id']) ?> • 
                        <span class="status-badge <?= $remaining_balance > 0 ? 'status-can-avail' : 'status-cannot-avail' ?>">
                            <?= $remaining_balance > 0 ? 'Can Avail' : 'Cannot Avail' ?>
                        </span> • 
                        <span class="money-status">₱<?= number_format($remaining_balance, 2) ?></span> • 
                        <span class="validity-status"><?= htmlspecialchars($validity_info) ?></span>
                    </p>
                </div>

                <div class="card-body">
                    <div class="details-grid">
                        <!-- Personal Information -->
                        <div class="detail-section">
                            <h3 class="section-title">Personal Information</h3>
                            <div class="detail-item">
                                <span class="detail-label">Full Name:</span>
                                <span class="detail-value"><?= htmlspecialchars($beneficiary['full_name']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Birthday:</span>
                                <span class="detail-value"><?= htmlspecialchars(date('F j, Y', strtotime($beneficiary['birthday']))) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Age:</span>
                                <span class="detail-value"><?= htmlspecialchars($beneficiary['age']) ?> years old</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Gender:</span>
                                <span class="detail-value"><?= htmlspecialchars($beneficiary['gender']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Civil Status:</span>
                                <span class="detail-value"><?= htmlspecialchars($beneficiary['civil_status']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Religion:</span>
                                <span class="detail-value"><?= htmlspecialchars($beneficiary['religion']) ?></span>
                            </div>
                        </div>

                        <!-- Background Information -->
                        <div class="detail-section">
                            <h3 class="section-title">Background Information</h3>
                            <div class="detail-item">
                                <span class="detail-label">Birthplace:</span>
                                <span class="detail-value"><?= htmlspecialchars($beneficiary['birthplace']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Education:</span>
                                <span class="detail-value"><?= htmlspecialchars($beneficiary['education']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Occupation:</span>
                                <span class="detail-value"><?= htmlspecialchars($beneficiary['occupation']) ?></span>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="detail-section">
                            <h3 class="section-title">Location Information</h3>
                            <div class="detail-item">
                                <span class="detail-label">Barangay:</span>
                                <span class="detail-value"><?= htmlspecialchars($beneficiary['barangay']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Sitio/Purok:</span>
                                <span class="detail-value"><?= htmlspecialchars($beneficiary['sitio']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Date Added:</span>
                                <span class="detail-value"><?= htmlspecialchars(date('F j, Y', strtotime($beneficiary['date_added']))) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Relatives Information -->
                    <div class="detail-section">
                        <h3 class="section-title">Family Members/Relatives</h3>
                        <div class="relatives-table-container">
                            <table class="relatives-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Age</th>
                                        <th>Civil Status</th>
                                        <th>Relationship</th>
                                        <th>Educational Attainment</th>
                                        <th>Occupation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($relatives) > 0): ?>
                                        <?php foreach ($relatives as $relative): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($relative['name']) ?></td>
                                                <td><?= htmlspecialchars($relative['age']) ?></td>
                                                <td><?= htmlspecialchars($relative['civil_status']) ?></td>
                                                <td><?= htmlspecialchars($relative['relationship']) ?></td>
                                                <td><?= htmlspecialchars($relative['educational_attainment'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($relative['occupation'] ?? 'N/A') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="no-relatives">No relatives recorded</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="index.php" class="btn btn-secondary">
                            ← Back to List
                        </a>
                        <a href="edit.php?id=<?= $beneficiary['id'] ?>" class="btn btn-primary">
                            ✏️ Edit Beneficiary
                        </a>
                        <a href="manage_relatives.php?beneficiary_id=<?= $beneficiary['id'] ?>" class="btn btn-info">
                            👥 Manage Relatives
                        </a>
                        <a href="../history/view.php?id=<?= $beneficiary['id'] ?>" class="btn btn-info">
                            🕛 History
                        </a>
                        <?php if ($remaining_balance > 0): ?>
                            <a href="select_service.php?beneficiary_id=<?= $beneficiary['id'] ?>" class="btn btn-success">
                                📝 Make Application
                            </a>
                        <?php else: ?>
                            <button class="btn btn-success" disabled title="No available balance">
                                📝 Make Application
                            </button>
                        <?php endif; ?>
                        <a href="requirements.php?beneficiary_id=<?= $beneficiary['id'] ?>" class="btn btn-warning">
                            📋 Requirements
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
