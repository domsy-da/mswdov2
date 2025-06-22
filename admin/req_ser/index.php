<?php
require_once '../../includes/db.php';

$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_service') {
        $name = trim($_POST['service_name']);
        $desc = trim($_POST['service_desc']);
        if ($name === '') {
            $errors[] = "Service name is required.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO services (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $desc]);
            $success = "Service added.";
        }
    }
    // Edit Service
    if (isset($_POST['action']) && $_POST['action'] === 'edit_service') {
        $id = intval($_POST['service_id']);
        $name = trim($_POST['service_name']);
        $desc = trim($_POST['service_desc']);
        if ($name === '') {
            $errors[] = "Service name is required.";
        } else {
            $stmt = $pdo->prepare("UPDATE services SET name=?, description=? WHERE id=?");
            $stmt->execute([$name, $desc, $id]);
            $success = "Service updated.";
        }
    }
    // Delete Service
    if (isset($_POST['action']) && $_POST['action'] === 'delete_service') {
        $id = intval($_POST['service_id']);
        // Delete requirements first due to FK constraint
        $pdo->prepare("DELETE FROM requirements WHERE service_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM services WHERE id=?")->execute([$id]);
        $success = "Service deleted.";
    }
    // Add Requirement
    if (isset($_POST['action']) && $_POST['action'] === 'add_requirement') {
        $service_id = intval($_POST['service_id']);
        $req_name = trim($_POST['requirement_name']);
        if ($req_name === '') {
            $errors[] = "Requirement name is required.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO requirements (service_id, requirement_name) VALUES (?, ?)");
            $stmt->execute([$service_id, $req_name]);
            $success = "Requirement added.";
        }
    }
    // Edit Requirement
    if (isset($_POST['action']) && $_POST['action'] === 'edit_requirement') {
        $id = intval($_POST['requirement_id']);
        $req_name = trim($_POST['requirement_name']);
        if ($req_name === '') {
            $errors[] = "Requirement name is required.";
        } else {
            $stmt = $pdo->prepare("UPDATE requirements SET requirement_name=? WHERE id=?");
            $stmt->execute([$req_name, $id]);
            $success = "Requirement updated.";
        }
    }
    // Delete Requirement
    if (isset($_POST['action']) && $_POST['action'] === 'delete_requirement') {
        $id = intval($_POST['requirement_id']);
        $pdo->prepare("DELETE FROM requirements WHERE id=?")->execute([$id]);
        $success = "Requirement deleted.";
    }
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch Services and Requirements
$services = $pdo->query("SELECT * FROM services ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$requirements = [];
if ($services) {
    $service_ids = array_column($services, 'id');
    if ($service_ids) {
        $in = str_repeat('?,', count($service_ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT * FROM requirements WHERE service_id IN ($in) ORDER BY requirement_name ASC");
        $stmt->execute($service_ids);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $req) {
            $requirements[$req['service_id']][] = $req;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service & Requirements Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <script>
        function toggleEditService(id) {
            document.getElementById('service-view-'+id).style.display = 'none';
            document.getElementById('service-edit-'+id).style.display = 'block';
        }
        function cancelEditService(id) {
            document.getElementById('service-view-'+id).style.display = 'block';
            document.getElementById('service-edit-'+id).style.display = 'none';
        }
        function toggleEditReq(id) {
            document.getElementById('req-view-'+id).style.display = 'none';
            document.getElementById('req-edit-'+id).style.display = 'flex';
        }
        function cancelEditReq(id) {
            document.getElementById('req-view-'+id).style.display = 'flex';
            document.getElementById('req-edit-'+id).style.display = 'none';
        }
    </script>
</head>
<body>
<div class="container">
    <a href="javascript:history.back()" class="back-btn">&larr; Back</a>
    <h1>Services &amp; Requirements</h1>

    <?php if ($success): ?>
        <div class="msg msg-success"><?=htmlspecialchars($success)?></div>
    <?php endif; ?>
    <?php foreach ($errors as $err): ?>
        <div class="msg msg-error"><?=htmlspecialchars($err)?></div>
    <?php endforeach; ?>

    <!-- Add Service -->
    <div class="card">
        <form class="form-inline" method="post" autocomplete="off">
            <input type="hidden" name="action" value="add_service">
            <input type="text" name="service_name" placeholder="Service name" required>
            <textarea name="service_desc" placeholder="Description" style="width:180px"></textarea>
            <button class="btn" type="submit">Add Service</button>
        </form>
    </div>

    <!-- Add Requirement -->
    <div class="card">
        <form class="form-inline" method="post" autocomplete="off">
            <input type="hidden" name="action" value="add_requirement">
            <select name="service_id" required>
                <option value="">Select Service</option>
                <?php foreach ($services as $srv): ?>
                    <option value="<?=$srv['id']?>"><?=htmlspecialchars($srv['name'])?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="requirement_name" placeholder="Requirement name" required>
            <button class="btn" type="submit">Add Requirement</button>
        </form>
    </div>

    <div class="divider"></div>

    <!-- List Services -->
    <?php foreach ($services as $srv): ?>
        <div class="card">
            <div id="service-view-<?=$srv['id']?>" class="service-header">
                <div>
                    <span class="service-title"><?=htmlspecialchars($srv['name'])?></span>
                    <div class="service-desc"><?=nl2br(htmlspecialchars($srv['description']))?></div>
                </div>
                <div class="actions">
                    <button class="btn btn-secondary" onclick="toggleEditService(<?=$srv['id']?>)">Edit</button>
                    <form method="post" style="display:inline" onsubmit="return confirm('Delete this service and all its requirements?');">
                        <input type="hidden" name="action" value="delete_service">
                        <input type="hidden" name="service_id" value="<?=$srv['id']?>">
                        <button class="btn" type="submit">Delete</button>
                    </form>
                </div>
            </div>
            <form id="service-edit-<?=$srv['id']?>" class="form-inline" method="post" style="display:none;margin-top:10px;">
                <input type="hidden" name="action" value="edit_service">
                <input type="hidden" name="service_id" value="<?=$srv['id']?>">
                <input type="text" name="service_name" value="<?=htmlspecialchars($srv['name'])?>" required>
                <textarea name="service_desc" style="width:180px"><?=htmlspecialchars($srv['description'])?></textarea>
                <button class="btn" type="submit">Save</button>
                <button class="btn btn-secondary" type="button" onclick="cancelEditService(<?=$srv['id']?>)">Cancel</button>
            </form>
            <ul class="req-list">
                <?php if (!empty($requirements[$srv['id']])): ?>
                    <?php foreach ($requirements[$srv['id']] as $req): ?>
                        <li class="req-item">
                            <div id="req-view-<?=$req['id']?>" style="display:flex;align-items:center;justify-content:space-between;width:100%;">
                                <span><?=htmlspecialchars($req['requirement_name'])?></span>
                                <div class="actions">
                                    <button class="btn btn-secondary" onclick="toggleEditReq(<?=$req['id']?>)">Edit</button>
                                    <form method="post" style="display:inline" onsubmit="return confirm('Delete this requirement?');">
                                        <input type="hidden" name="action" value="delete_requirement">
                                        <input type="hidden" name="requirement_id" value="<?=$req['id']?>">
                                        <button class="btn" type="submit">Delete</button>
                                    </form>
                                </div>
                            </div>
                            <form id="req-edit-<?=$req['id']?>" class="form-inline" method="post" style="display:none;width:100%;margin-top:0;">
                                <input type="hidden" name="action" value="edit_requirement">
                                <input type="hidden" name="requirement_id" value="<?=$req['id']?>">
                                <input type="text" name="requirement_name" value="<?=htmlspecialchars($req['requirement_name'])?>" required>
                                <button class="btn" type="submit">Save</button>
                                <button class="btn btn-secondary" type="button" onclick="cancelEditReq(<?=$req['id']?>)">Cancel</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="req-item" style="background:#f7f8fa;color:#888;">No requirements.</li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endforeach; ?>

</div>
</body>
</html>