<?php 
require_once '../includes/db.php';

// Get beneficiary ID from URL
$beneficiary_id = isset($_GET['beneficiary_id']) ? (int)$_GET['beneficiary_id'] : 0;

// Fetch beneficiary details
$stmt = $pdo->prepare("SELECT full_name FROM beneficiaries WHERE id = ?");
$stmt->execute([$beneficiary_id]);
$beneficiary = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all relatives
$stmt = $pdo->prepare("SELECT * FROM relatives WHERE beneficiary_id = ?");
$stmt->execute([$beneficiary_id]);
$relatives = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Relatives</title>
    <link rel="stylesheet" href="../assets/css/manage_relatives.css">
</head>
<body>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <div class="header-left">
                    <a href="view.php?id=<?= $beneficiary_id ?>" class="back-button">
                        <span class="back-arrow">←</span> Back to Beneficiary
                    </a>
                    <h1>Manage Relatives</h1>
                    <p>Managing relatives for: <?= htmlspecialchars($beneficiary['full_name']) ?></p>
                </div>
                <button class="btn btn-primary" onclick="openModal('add')">+ Add New Relative</button>
            </div>

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
                            <th>Actions</th>
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
                                    <td>
                                        <button onclick="editRelative(<?= $relative['id'] ?>)" class="btn btn-small btn-edit">Edit</button>
                                        <button onclick="deleteRelative(<?= $relative['id'] ?>)" class="btn btn-small btn-delete">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">No relatives found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal for Add/Edit Relative -->
    <div class="modal" id="relativeModal">
        <div class="modal-content">
            <h2 id="modalTitle">Add New Relative</h2>
            <form id="relativeForm">
                <input type="hidden" id="relative_id" name="relative_id">
                <input type="hidden" name="beneficiary_id" value="<?= $beneficiary_id ?>">
                
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="age">Age:</label>
                    <input type="number" id="age" name="age" required>
                </div>

                <div class="form-group">
                    <label for="civil_status">Civil Status:</label>
                    <select id="civil_status" name="civil_status" required>
                        <option value="">Select Status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widowed">Widowed</option>
                        <option value="Separated">Separated</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="relationship">Relationship:</label>
                    <select name="relationship" id="relationship">
                        <option value="">Select Relationship</option>
                        <option value="Child">Child</option>
                        <option value="Spouse">Spouse</option>
                        <option value="Parent">Parent</option>
                        <option value="Sibling">Sibling</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="educational_attainment">Educational Attainment:</label>
                    <select name="educational_attainment" id="educational_attainment">
                        <option value="">Select Educational Attainment</option>
                        <option value="None">None</option>
                        <option value="Elementary">Elementary</option>
                        <option value="Primary">Primary</option>
                        <option value="Secondary">Secondary</option>
                        <option value="Tertiary">Tertiary</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="occupation">Occupation:</label>
                    <input type="text" id="occupation" name="occupation" value="Unemployed">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/relatives.js"></script>
</body>
</html>