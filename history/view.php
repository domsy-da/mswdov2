<?php
require_once '../includes/db.php';
require_once '../includes/auth_check.php';

// Get beneficiary ID from URL
$beneficiary_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$beneficiary_id) {
    header('Location: index.php');
    exit();
}

// Get all transactions for this beneficiary
$query = "SELECT t.*, 
          DATE_FORMAT(t.request_date, '%M %d, %Y') as formatted_date,
          DATE_FORMAT(t.created_at, '%M %d, %Y %h:%i %p') as formatted_created_at
          FROM transactions t
          WHERE t.beneficiary_id = ?
          ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$beneficiary_id]);
$transactions = $stmt->fetchAll();

// Get beneficiary details from first transaction
$client_info = !empty($transactions) ? $transactions[0] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - MSWDO</title>
    <style>
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .client-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .client-name {
            font-size: 1.5rem;
            margin: 0 0 10px 0;
            color: #333;
        }

        .client-details {
            color: #666;
            font-size: 0.9rem;
        }

        .transactions {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .transaction-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            gap: 20px;
            align-items: start;
        }

        .transaction-date {
            color: #666;
            font-size: 0.9rem;
        }

        .transaction-details h3 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .transaction-details p {
            margin: 5px 0;
            color: #666;
        }

        .transaction-amount {
            text-align: right;
            font-weight: 600;
            color: #333;
        }

        .back-link {
            display: inline-block;
            padding: 10px 20px;
            background: #333;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .back-link:hover {
            background: #000;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <a href="index.php" class="back-link">← Back to History</a>

        <?php if ($client_info): ?>
        <div class="client-header">
            <h2 class="client-name"><?= htmlspecialchars($client_info['client_name']) ?></h2>
            <div class="client-details">
                <p>Age: <?= htmlspecialchars($client_info['client_age']) ?> • 
                   Civil Status: <?= htmlspecialchars($client_info['client_civil_status']) ?></p>
                <p>Address: <?= htmlspecialchars($client_info['client_complete_address']) ?></p>
            </div>
        </div>

        <div class="transactions">
            <?php if (!empty($transactions)): ?>
                <?php foreach ($transactions as $transaction): ?>
                    <div class="transaction-item">
                        <div class="transaction-date">
                            <strong><?= $transaction['formatted_date'] ?></strong><br>
                            <small><?= $transaction['formatted_created_at'] ?></small>
                        </div>
                        <div class="transaction-details">
                            <h3><?= htmlspecialchars($transaction['request_purpose']) ?></h3>
                            <p>Patient: <?= htmlspecialchars($transaction['patient_name']) ?></p>
                            <?php if ($transaction['diagnosis_school']): ?>
                                <p>Diagnosis/School: <?= htmlspecialchars($transaction['diagnosis_school']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="transaction-amount">
                            ₱<?= number_format($transaction['amount'], 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No transactions found for this client.</p>
                </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <p>Client not found.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Background Logo -->
    <div style="
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 120%;
        height: 120%;
        z-index: -1;
        opacity: 0.05;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    ">
        <img src="../assets/img/mswdologo2.jpg" alt="" style="
            width: 100%;
            height: 100%;
            object-fit: contain;
        ">
    </div>
</body>
</html>