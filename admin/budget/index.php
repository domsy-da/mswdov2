<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

checkAdminAccess();

// Handle form submission for new budget
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_budget'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO budgets (amount, remaining_amount, description, start_date) 
                              VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['amount'],
            $_POST['amount'], // Initially, remaining amount equals total amount
            $_POST['description'],
            $_POST['start_date']
        ]);
        $success_message = "Budget added successfully!";
    } catch (PDOException $e) {
        $error_message = "Error adding budget: " . $e->getMessage();
    }
}

// Add this after your existing queries
$allocation_settings = $pdo->query("SELECT * FROM allocation_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Add this to your form handling section at the top
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_allocation'])) {
    try {
        if ($allocation_settings) {
            // Update existing settings
            $stmt = $pdo->prepare("UPDATE allocation_settings SET default_amount = ?, validity_months = ?");
        } else {
            // Insert new settings
            $stmt = $pdo->prepare("INSERT INTO allocation_settings (default_amount, validity_months) VALUES (?, ?)");
        }
        
        $stmt->execute([
            $_POST['default_amount'],
            $_POST['validity_months']
        ]);
        $allocation_success = "Default allocation settings updated successfully!";
    } catch (PDOException $e) {
        $allocation_error = "Error updating allocation settings: " . $e->getMessage();
    }
}

// Get budget statistics
$stats = [
    'daily' => $pdo->query("SELECT SUM(amount_used) as total FROM budget_transactions 
                           WHERE DATE(transaction_date) = CURDATE()")->fetch(PDO::FETCH_ASSOC),
    'weekly' => $pdo->query("SELECT SUM(amount_used) as total FROM budget_transactions 
                            WHERE YEARWEEK(transaction_date) = YEARWEEK(NOW())")->fetch(PDO::FETCH_ASSOC),
    'monthly' => $pdo->query("SELECT SUM(amount_used) as total FROM budget_transactions 
                             WHERE MONTH(transaction_date) = MONTH(NOW()) 
                             AND YEAR(transaction_date) = YEAR(NOW())")->fetch(PDO::FETCH_ASSOC)
];

// Get current budget and transactions
$current_budget = $pdo->query("SELECT * FROM budgets ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Get recent transactions
$recent_transactions = $pdo->query("
    SELECT bt.*, t.request_purpose, t.amount, b.amount as budget_amount
    FROM budget_transactions bt
    JOIN transactions t ON bt.transaction_id = t.id
    JOIN budgets b ON bt.budget_id = b.id
    ORDER BY bt.transaction_date DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Budget Management - MSWDO</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #333;
            color: #fff;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: all 0.2s ease;
            z-index: 1000;
        }

        .back-button:hover {
            background: #000;
        }

        .budget-container {
            max-width: 1200px;
            margin: 60px auto 20px;
            padding: 0 1rem;
        }

        .page-header h1 {
            color: #333;
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        .budget-form-container {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .budget-form {
            display: grid;
            gap: 1rem;
        }

        .form-group input, 
        .form-group textarea {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .submit-btn {
            background: #333;
            color: #fff;
            border: none;
            padding: 0.75rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .submit-btn:hover {
            background: #000;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .transactions-table th {
            background: #333;
            color: #fff;
            font-weight: 500;
            padding: 1rem;
            text-align: left;
        }

        .transactions-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .transactions-table tr:hover {
            background: #f8f8f8;
        }

        .budget-overview {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .overview-card {
            background: #333;
            color: #fff;
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .overview-card h3 {
            margin: 0 0 1rem 0;
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .overview-card .amount {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
            color: #fff;
        }

        .overview-card.total {
            background: linear-gradient(135deg, #333 0%, #4a4a4a 100%);
        }

        .overview-card.remaining {
            background: linear-gradient(135deg, #4a4a4a 0%, #333 100%);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .budget-form-container {
            margin-bottom: 0; /* Remove bottom margin since grid handles spacing */
        }

        .success-message {
            background: #333;
            color: #fff;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-button">← Back</a>

    <div class="budget-container">
        <div class="page-header">
            <h1>Budget Management</h1>
        </div>

        <!-- Budget Overview -->
        <div class="budget-overview">
            <div class="overview-card total">
                <h3>Total Budget</h3>
                <p class="amount">₱<?= number_format($current_budget['amount'] ?? 0, 2) ?></p>
            </div>
            <div class="overview-card remaining">
                <h3>Remaining Budget</h3>
                <p class="amount">₱<?= number_format($current_budget['remaining_amount'] ?? 0, 2) ?></p>
            </div>
        </div>

        <!-- Budget Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Daily Spending</h3>
                <p class="amount">₱<?= number_format($stats['daily']['total'] ?? 0, 2) ?></p>
            </div>
            <div class="stat-card">
                <h3>Weekly Spending</h3>
                <p class="amount">₱<?= number_format($stats['weekly']['total'] ?? 0, 2) ?></p>
            </div>
            <div class="stat-card">
                <h3>Monthly Spending</h3>
                <p class="amount">₱<?= number_format($stats['monthly']['total'] ?? 0, 2) ?></p>
            </div>
        </div>

        <!-- Add New Budget and Allocation Settings Form -->
        <div class="form-grid">
            <!-- Budget Form -->
            <div class="budget-form-container">
                <h2>Add New Budget</h2>
                <form method="POST" class="budget-form">
                    <div class="form-group">
                        <label>Amount:</label>
                        <input type="number" name="amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Start Date:</label>
                        <input type="date" name="start_date" required>
                    </div>
                    <button type="submit" name="add_budget" class="submit-btn">Add Budget</button>
                </form>
            </div>

            <!-- Allocation Settings Form -->
            <div class="budget-form-container">
                <h2>Default Allocation Settings</h2>
                <?php if (isset($allocation_success)): ?>
                    <div class="success-message"><?= htmlspecialchars($allocation_success) ?></div>
                <?php endif; ?>
                
                <form method="POST" class="budget-form">
                    <div class="form-group">
                        <label>Default Amount per Beneficiary:</label>
                        <input type="number" 
                               name="default_amount" 
                               step="0.01" 
                               value="<?= htmlspecialchars($allocation_settings['default_amount'] ?? '') ?>" 
                               required>
                    </div>
                    <div class="form-group">
                        <label>Validity (Months):</label>
                        <input type="number" 
                               name="validity_months" 
                               min="1" 
                               max="60" 
                               value="<?= htmlspecialchars($allocation_settings['validity_months'] ?? '') ?>" 
                               required>
                    </div>
                    <button type="submit" name="update_allocation" class="submit-btn">
                        <?= $allocation_settings ? 'Update Settings' : 'Save Settings' ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="transactions-container">
            <h2>Recent Transactions</h2>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Purpose</th>
                        <th>Amount Used</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_transactions as $transaction): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($transaction['transaction_date'])) ?></td>
                            <td><?= htmlspecialchars($transaction['request_purpose']) ?></td>
                            <td>₱<?= number_format($transaction['amount_used'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    

</body>
</html>
