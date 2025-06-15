<?php
require_once '../../includes/db.php';
require_once '../../includes/auth_check.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch attribute details
$stmt = $pdo->prepare("
    SELECT a.*, b.name as barangay_name, s.name as sitio_name 
    FROM barangay_sitio_attributes a 
    LEFT JOIN barangays b ON a.barangay_id = b.id 
    LEFT JOIN sitios s ON a.sitio_id = s.id 
    WHERE a.id = ?
");
$stmt->execute([$id]);
$attr = $stmt->fetch();

if (!$attr) {
    header('Location: index.php');
    exit();
}

// Fetch barangays and sitios for dropdowns
$barangays = $pdo->query("SELECT id, name FROM barangays ORDER BY name")->fetchAll();
$sitios = $pdo->query("SELECT id, name, barangay_id FROM sitios ORDER BY barangay_id, name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attributes - <?= htmlspecialchars($attr['barangay_name']) ?></title>
    <link rel="stylesheet" href="recommended_beneficiaries.css">
    <style>
        /* Copy the styles from index.php */
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* ... copy other styles from index.php ... */
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Edit Location Attributes</h1>
            <a href="index.php" class="btn btn-secondary">← Back</a>
        </div>

        <form id="editForm" method="POST">
            <input type="hidden" name="id" value="<?= $attr['id'] ?>">

            <div class="form-group">
                <label>Barangay:</label>
                <select name="barangay_id" required>
                    <option value="">-- Select Barangay --</option>
                    <?php foreach ($barangays as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $b['id'] == $attr['barangay_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Sitio (optional):</label>
                <select name="sitio_id" id="sitio_id">
                    <option value="">-- Select Sitio --</option>
                    <?php foreach ($sitios as $s): 
                        if ($s['barangay_id'] == $attr['barangay_id']): ?>
                            <option value="<?= $s['id'] ?>" <?= $s['id'] == $attr['sitio_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['name']) ?>
                            </option>
                    <?php endif; endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Road Access:</label>
                <select name="road_access" required>
                    <?php 
                    $options = ['Paved Road', 'Gravel Road', 'Footpath Only'];
                    foreach ($options as $option): ?>
                        <option value="<?= $option ?>" <?= $attr['road_access'] === $option ? 'selected' : '' ?>>
                            <?= $option ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Travel Time to Market (minutes):</label>
                <input type="number" name="travel_time_to_market" min="0" 
                       value="<?= $attr['travel_time_to_market'] ?>" required>
            </div>

            <div class="form-group">
                <label>Distance to Town (km):</label>
                <input type="number" name="distance_km" step="0.1" min="0" 
                       value="<?= $attr['distance_km'] ?>" required>
            </div>

            <div class="form-group">
                <label>Public Transport:</label>
                <select name="public_transport" required>
                    <?php 
                    $options = ['Available', 'Limited', 'None'];
                    foreach ($options as $option): ?>
                        <option value="<?= $option ?>" <?= $attr['public_transport'] === $option ? 'selected' : '' ?>>
                            <?= $option ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Communication Signal:</label>
                <select name="communication_signal" required>
                    <?php 
                    $options = ['Strong', 'Weak', 'None'];
                    foreach ($options as $option): ?>
                        <option value="<?= $option ?>" <?= $attr['communication_signal'] === $option ? 'selected' : '' ?>>
                            <?= $option ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="near_river" <?= $attr['near_river'] ? 'checked' : '' ?>> 
                    Near River
                </label>
                <label>
                    <input type="checkbox" name="near_ocean" <?= $attr['near_ocean'] ? 'checked' : '' ?>> 
                    Near Ocean
                </label>
                <label>
                    <input type="checkbox" name="near_forest" <?= $attr['near_forest'] ? 'checked' : '' ?>> 
                    Near Forest
                </label>
            </div>

            <div class="form-group">
                <label>Hazard Zone:</label>
                <select name="hazard_zone" required>
                    <?php 
                    $options = ['None', 'Flood-Prone', 'Landslide-Prone', 'Typhoon-Prone'];
                    foreach ($options as $option): ?>
                        <option value="<?= $option ?>" <?= $attr['hazard_zone'] === $option ? 'selected' : '' ?>>
                            <?= $option ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Remarks:</label>
                <textarea name="remarks" rows="3"><?= htmlspecialchars($attr['remarks']) ?></textarea>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Update Attributes</button>
            </div>
        </form>
    </div>

    <script>
    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('update_attribute.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Attributes updated successfully!');
                window.location.href = 'index.php';
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    });

    // Handle dynamic sitio loading
    document.querySelector('select[name="barangay_id"]').addEventListener('change', function() {
        const sitios = <?= json_encode($sitios) ?>;
        const sitioSelect = document.getElementById('sitio_id');
        const selectedBarangayId = this.value;
        
        sitioSelect.innerHTML = '<option value="">-- Select Sitio --</option>';
        
        if (selectedBarangayId) {
            const filteredSitios = sitios.filter(s => s.barangay_id === selectedBarangayId);
            filteredSitios.forEach(s => {
                sitioSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
            sitioSelect.disabled = false;
        } else {
            sitioSelect.disabled = true;
        }
    });
    </script>
</body>
</html>