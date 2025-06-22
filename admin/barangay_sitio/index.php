<?php
require_once '../../includes/db.php';

// Handle Add/Edit/Delete Actions
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add Barangay
    if (isset($_POST['add_barangay'])) {
        $name = trim($_POST['barangay_name']);
        if ($name === '') {
            $errors[] = "Barangay name cannot be empty.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO barangays (name) VALUES (?)");
            $stmt->execute([$name]);
            $success = "Barangay added.";
        }
    }

    // Add Sitio
    if (isset($_POST['add_sitio'])) {
        $barangay_id = intval($_POST['barangay_id']);
        $name = trim($_POST['sitio_name']);
        if ($name === '') {
            $errors[] = "Sitio name cannot be empty.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO sitios (barangay_id, name) VALUES (?, ?)");
            $stmt->execute([$barangay_id, $name]);
            $success = "Sitio added.";
        }
    }

    // Edit Barangay
    if (isset($_POST['edit_barangay'])) {
        $id = intval($_POST['barangay_id']);
        $name = trim($_POST['barangay_name']);
        if ($name === '') {
            $errors[] = "Barangay name cannot be empty.";
        } else {
            $stmt = $pdo->prepare("UPDATE barangays SET name=? WHERE id=?");
            $stmt->execute([$name, $id]);
            $success = "Barangay updated.";
        }
    }

    // Edit Sitio
    if (isset($_POST['edit_sitio'])) {
        $id = intval($_POST['sitio_id']);
        $name = trim($_POST['sitio_name']);
        if ($name === '') {
            $errors[] = "Sitio name cannot be empty.";
        } else {
            $stmt = $pdo->prepare("UPDATE sitios SET name=? WHERE id=?");
            $stmt->execute([$name, $id]);
            $success = "Sitio updated.";
        }
    }
}

// Handle Deletes
if (isset($_GET['delete_barangay'])) {
    $id = intval($_GET['delete_barangay']);
    // Delete sitios first (foreign key constraint)
    $pdo->prepare("DELETE FROM sitios WHERE barangay_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM barangays WHERE id=?")->execute([$id]);
    $success = "Barangay and its Sitios deleted.";
}
if (isset($_GET['delete_sitio'])) {
    $id = intval($_GET['delete_sitio']);
    $pdo->prepare("DELETE FROM sitios WHERE id=?")->execute([$id]);
    $success = "Sitio deleted.";
}

// Fetch Barangays and Sitios
$barangays = $pdo->query("SELECT * FROM barangays ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$sitios = [];
if ($barangays) {
    $barangay_ids = array_column($barangays, 'id');
    if ($barangay_ids) {
        $in = implode(',', array_fill(0, count($barangay_ids), '?'));
        $stmt = $pdo->prepare("SELECT * FROM sitios WHERE barangay_id IN ($in) ORDER BY name ASC");
        $stmt->execute($barangay_ids);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $sitio) {
            $sitios[$sitio['barangay_id']][] = $sitio;
        }
    }
}

// For editing
$edit_barangay = null;
if (isset($_GET['edit_barangay'])) {
    $id = intval($_GET['edit_barangay']);
    $stmt = $pdo->prepare("SELECT * FROM barangays WHERE id=?");
    $stmt->execute([$id]);
    $edit_barangay = $stmt->fetch(PDO::FETCH_ASSOC);
}
$edit_sitio = null;
if (isset($_GET['edit_sitio'])) {
    $id = intval($_GET['edit_sitio']);
    $stmt = $pdo->prepare("SELECT * FROM sitios WHERE id=?");
    $stmt->execute([$id]);
    $edit_sitio = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barangay & Sitio Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<div class="container">
    <a href="javascript:history.back()" class="back-btn">&larr; Back</a>
    <h1>Barangay & Sitio Management</h1>

    <?php if ($success): ?>
        <div class="message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php foreach ($errors as $err): ?>
        <div class="error"><?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>

    <!-- Add/Edit Barangay -->
    <div class="section">
        <h2 style="font-size:1.1rem;font-weight:500;margin-bottom:10px;">
            <?= $edit_barangay ? "Edit Barangay" : "Add Barangay" ?>
        </h2>
        <form method="post" class="form-inline" style="margin-bottom:0;">
            <input type="text" name="barangay_name" placeholder="Barangay name" required
                   value="<?= $edit_barangay ? htmlspecialchars($edit_barangay['name']) : '' ?>">
            <?php if ($edit_barangay): ?>
                <input type="hidden" name="barangay_id" value="<?= $edit_barangay['id'] ?>">
                <button type="submit" name="edit_barangay">Update</button>
                <a href="index.php" class="action-btn" style="color:#888;">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_barangay">Add</button>
            <?php endif; ?>
        </form>
    </div>

    <!-- Add/Edit Sitio -->
    <div class="section">
        <h2 style="font-size:1.1rem;font-weight:500;margin-bottom:10px;">
            <?= $edit_sitio ? "Edit Sitio" : "Add Sitio" ?>
        </h2>
        <form method="post" class="form-inline" style="margin-bottom:0;">
            <select name="barangay_id" required <?= $edit_sitio ? 'disabled' : '' ?>>
                <option value="">Select Barangay</option>
                <?php foreach ($barangays as $b): ?>
                    <option value="<?= $b['id'] ?>"
                        <?= ($edit_sitio && $edit_sitio['barangay_id'] == $b['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="sitio_name" placeholder="Sitio name" required
                   value="<?= $edit_sitio ? htmlspecialchars($edit_sitio['name']) : '' ?>">
            <?php if ($edit_sitio): ?>
                <input type="hidden" name="sitio_id" value="<?= $edit_sitio['id'] ?>">
                <input type="hidden" name="barangay_id" value="<?= $edit_sitio['barangay_id'] ?>">
                <button type="submit" name="edit_sitio">Update</button>
                <a href="index.php" class="action-btn" style="color:#888;">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_sitio">Add</button>
            <?php endif; ?>
        </form>
    </div>

    <!-- Barangay & Sitio List -->
    <div class="section">
        <ul class="barangay-list">
            <?php foreach ($barangays as $barangay): ?>
                <li class="barangay-item">
                    <div class="barangay-header">
                        <span class="barangay-name"><?= htmlspecialchars($barangay['name']) ?></span>
                        <span class="actions">
                            <a href="?edit_barangay=<?= $barangay['id'] ?>" class="action-btn" title="Edit">&#9998;</a>
                            <a href="?delete_barangay=<?= $barangay['id'] ?>"
                               class="action-btn"
                               title="Delete"
                               onclick="return confirm('Delete this Barangay and all its Sitios?');">&#128465;</a>
                        </span>
                    </div>
                    <?php if (!empty($sitios[$barangay['id']])): ?>
                        <ul class="sitio-list">
                            <?php foreach ($sitios[$barangay['id']] as $sitio): ?>
                                <li class="sitio-item">
                                    <span class="sitio-name"><?= htmlspecialchars($sitio['name']) ?></span>
                                    <span class="actions">
                                        <a href="?edit_sitio=<?= $sitio['id'] ?>" class="action-btn" title="Edit">&#9998;</a>
                                        <a href="?delete_sitio=<?= $sitio['id'] ?>"
                                           class="action-btn"
                                           title="Delete"
                                           onclick="return confirm('Delete this Sitio?');">&#128465;</a>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div style="color:#aaa;font-size:0.98rem;margin:8px 0 0 2px;">No Sitios.</div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            <?php if (empty($barangays)): ?>
                <li style="color:#888;font-size:1rem;">No Barangays found.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>
</body>
</html>