<?php
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once 'classes/RecommendationSystem.php';

$program_id = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;

if ($program_id <= 0) {
    header('Location: index.php');
    exit();
}

// Fetch program details
$stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
$stmt->execute([$program_id]);
$program = $stmt->fetch();
if (!$program) {
    header('Location: index.php');
    exit();
}

// Generate and get recommendations
$recSystem = new RecommendationSystem($pdo, $program_id);
$recommended = $recSystem->generateRecommendations($program_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommended Beneficiaries - MSWDO</title>
    <link rel="stylesheet" href="recommended_beneficiaries.css">
</head>
<body>
    
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Program Recommendations</h1>
                    <p class="page-subtitle">View potential beneficiaries for this program</p>
                </div>
                <div class="header-actions">
                    <a href="index.php" class="btn btn-secondary">
                        ← Back to Programs
                    </a>
                </div>
            </div>

            <div class="program-header">
                <h2><?= htmlspecialchars($program['program_name']) ?></h2>
                <p><?= htmlspecialchars($program['program_description']) ?></p>
                
                <div class="program-stats">
                    <div class="stat-item">
                        <span class="stat-label">Program Type</span>
                        <span class="stat-value"><?= htmlspecialchars($program['program_type']) ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Target Beneficiaries</span>
                        <span class="stat-value"><?= number_format($program['target_beneficiaries']) ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Created Date</span>
                        <span class="stat-value"><?= date('M d, Y', strtotime($program['created_at'])) ?></span>
                    </div>
                </div>
            </div>

            <table class="beneficiaries-table">
                <thead>
                    <tr>
                        <th>Beneficiary Name</th>
                        <th>Address</th>
                        <th>Age</th>
                        <th>Civil Status</th>
                        <th>Scores</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recommended)): ?>
                        <?php foreach ($recommended as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['full_name']) ?></td>
                                <td><?= htmlspecialchars($b['sitio'] . ', ' . $b['barangay']) ?></td>
                                <td><?= htmlspecialchars($b['age']) ?></td>
                                <td><?= htmlspecialchars($b['civil_status']) ?></td>
                                <td>
                                    <span class="badge">
                                        Score: <?= number_format($b['eligibility_score'] * 100, 1) ?>%
                                    </span>
                                    <button onclick="toggleReason(this)" style="border:none; background:none; cursor:pointer;">
                                        🔍
                                    </button>
                                    <div class="reason-text" style="display:none; margin-top:5px; font-size: 0.8em;">
                                        <?= htmlspecialchars($b['recommendation_reason']) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No recommended beneficiaries found for this program.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    
<script>
function toggleReason(btn) {
    const reasonDiv = btn.nextElementSibling;
    if (reasonDiv.style.display === "none") {
        reasonDiv.style.display = "block";
    } else {
        reasonDiv.style.display = "none";
    }
}
</script>

</body>
</html>