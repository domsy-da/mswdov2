<?php
include '../includes/db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'distribute') {
    $beneficiary_id = $_POST['beneficiary_id'];
    $resource_id = $_POST['resource_id'];
    $quantity_given = $_POST['quantity_given'];

    $stmt = $pdo->prepare("INSERT INTO distributions (beneficiary_id, resource_id, quantity_given) VALUES (?, ?, ?)");
    $stmt->execute([$beneficiary_id, $resource_id, $quantity_given]);

    // Optionally update resource quantity:
    $updateStmt = $pdo->prepare("UPDATE resources SET quantity = quantity - ? WHERE id = ?");
    $updateStmt->execute([$quantity_given, $resource_id]);

    header('Location: distribution.php');
    exit;
}

// Fetch beneficiaries
$beneficiaries = $pdo->query("SELECT id, full_name FROM beneficiaries ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch resources
$resources = $pdo->query("SELECT id, name, quantity FROM resources ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing distributions
$distributions = $pdo->query("
    SELECT d.*, b.full_name, r.name AS resource_name
    FROM distributions d
    JOIN beneficiaries b ON d.beneficiary_id = b.id
    JOIN resources r ON d.resource_id = r.id
    ORDER BY d.date_given DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Distribute Resources - MSWDO Management System</title>
    <link rel="stylesheet" href="resources.css" />
</head>
<body>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Distribute Resources</h1>
                <p class="page-subtitle">Assign resources to beneficiaries</p>
            </div>
            <div class="header-actions">
                <a href="index.php" class="btn btn-secondary">← Back to Resources</a>
            </div>
        </div>

        <!-- Distribution Form -->
        <form method="POST" class="form">
            <h2 class="form-title">New Distribution</h2>
            <input type="hidden" name="action" value="distribute" />

            <div class="form-group">
                <label for="beneficiary_id">Select Beneficiary:</label>
                <select name="beneficiary_id" id="beneficiary_id" required>
                    <option value="">-- Select Beneficiary --</option>
                    <?php foreach ($beneficiaries as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="resource_id">Select Resource:</label>
                <select name="resource_id" id="resource_id" required>
                    <option value="">-- Select Resource --</option>
                    <?php foreach ($resources as $r): ?>
                        <option value="<?= $r['id'] ?>">
                            <?= htmlspecialchars($r['name']) ?> (Available: <?= $r['quantity'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="quantity_given">Quantity to Give:</label>
                <input type="number" name="quantity_given" id="quantity_given" min="1" required />
            </div>

            <button type="submit" class="btn btn-primary">Distribute Resource</button>
        </form>

        <!-- Existing Distributions Table -->
        <div class="table-container" style="margin-top:2rem;">
            <h2 class="table-title">Distribution History</h2>
            <table class="beneficiaries-table">
                <thead>
                    <tr>
                        <th>Beneficiary</th>
                        <th>Resource</th>
                        <th>Quantity Given</th>
                        <th>Date Given</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($distributions) > 0): ?>
                        <?php foreach ($distributions as $dist): ?>
                            <tr>
                                <td><?= htmlspecialchars($dist['full_name']) ?></td>
                                <td><?= htmlspecialchars($dist['resource_name']) ?></td>
                                <td><?= htmlspecialchars($dist['quantity_given']) ?></td>
                                <td><?= htmlspecialchars($dist['date_given']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-state">No distributions yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

</body>
</html>
