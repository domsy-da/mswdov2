<?php
// --- mark_attributes.php ---
// Make sure this points to your PDO connection
include '../../includes/db.php'; 

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = "INSERT INTO barangay_sitio_attributes 
            (barangay_id, sitio_id, road_access, travel_time_to_market, distance_km, 
            public_transport, communication_signal, near_river, near_ocean, near_forest, 
            hazard_zone, remarks)
            VALUES 
            (:barangay_id, :sitio_id, :road_access, :travel_time_to_market, :distance_km, 
            :public_transport, :communication_signal, :near_river, :near_ocean, :near_forest, 
            :hazard_zone, :remarks)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':barangay_id' => $_POST['barangay_id'],
            ':sitio_id' => !empty($_POST['sitio_id']) ? $_POST['sitio_id'] : null,
            ':road_access' => $_POST['road_access'],
            ':travel_time_to_market' => $_POST['travel_time_to_market'],
            ':distance_km' => $_POST['distance_km'],
            ':public_transport' => $_POST['public_transport'],
            ':communication_signal' => $_POST['communication_signal'],
            ':near_river' => isset($_POST['near_river']) ? 1 : 0,
            ':near_ocean' => isset($_POST['near_ocean']) ? 1 : 0,
            ':near_forest' => isset($_POST['near_forest']) ? 1 : 0,
            ':hazard_zone' => $_POST['hazard_zone'],
            ':remarks' => $_POST['remarks']
        ]);
        
        // Return success response as JSON
        echo json_encode(['status' => 'success', 'message' => 'Attributes saved successfully!']);
        exit;
    } catch (PDOException $e) {
        // Return error response as JSON
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// --- Fetch Barangays and Sitios ---
try {
    $barangays = $pdo->query("SELECT id, name FROM barangays ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $sitios = $pdo->query("SELECT id, name, barangay_id FROM sitios ORDER BY barangay_id, name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mark Barangay & Sitio Attributes</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 1.8rem;
            margin: 0;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 500;
        }

        select, 
        input[type="number"], 
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            margin-top: 5px;
        }

        .checkbox-group {
            display: flex;
            gap: 20px;
            margin: 15px 0;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #333;
            color: white;
        }

        .btn-secondary {
            background: #666;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .records-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .records-section h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .records-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 4px;
        }

        .records-table th,
        .records-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .records-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .records-table tr:hover {
            background: #f8f9fa;
        }

        .actions-cell {
            white-space: nowrap;
            width: 100px;
        }

        .btn-icon {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            margin: 0 2px;
            border-radius: 4px;
        }

        .btn-icon:hover {
            background: #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Mark Barangay & Sitio Attributes</h1>
            <a href="../index.php" class="btn btn-secondary">← Back</a>
        </div>

        <form id="attributesForm" method="POST">
            <div class="form-group">
                <label>Barangay:</label>
                <select name="barangay_id" required>
                    <option value="">-- Select Barangay --</option>
                    <?php foreach ($barangays as $b): ?>
                        <option value="<?= htmlspecialchars($b['id']) ?>">
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Sitio (optional):</label>
                <select name="sitio_id" id="sitio_id" disabled>
                    <option value="">-- Select Barangay First --</option>
                </select>
            </div>

            <div class="form-group">
                <label>Road Access:</label>
                <select name="road_access" required>
                    <option value="Paved Road">Paved Road</option>
                    <option value="Gravel Road">Gravel Road</option>
                    <option value="Footpath Only">Footpath Only</option>
                </select>
            </div>

            <div class="form-group">
                <label>Travel Time to Market (minutes):</label>
                <input type="number" name="travel_time_to_market" min="0" required>
            </div>

            <div class="form-group">
                <label>Distance to Town (km):</label>
                <input type="number" name="distance_km" step="0.1" min="0" required>
            </div>

            <div class="form-group">
                <label>Public Transport:</label>
                <select name="public_transport" required>
                    <option value="Available">Available</option>
                    <option value="Limited">Limited</option>
                    <option value="None">None</option>
                </select>
            </div>

            <div class="form-group">
                <label>Communication Signal:</label>
                <select name="communication_signal" required>
                    <option value="Strong">Strong</option>
                    <option value="Weak">Weak</option>
                    <option value="None">None</option>
                </select>
            </div>

            <div class="checkbox-group">
                <label><input type="checkbox" name="near_river"> Near River</label>
                <label><input type="checkbox" name="near_ocean"> Near Ocean</label>
                <label><input type="checkbox" name="near_forest"> Near Forest</label>
            </div>

            <div class="form-group">
                <label>Hazard Zone:</label>
                <select name="hazard_zone" required>
                    <option value="None">None</option>
                    <option value="Flood-Prone">Flood-Prone</option>
                    <option value="Landslide-Prone">Landslide-Prone</option>
                    <option value="Typhoon-Prone">Typhoon-Prone</option>
                </select>
            </div>

            <div class="form-group">
                <label>Remarks:</label>
                <textarea name="remarks" rows="3"></textarea>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Save Attributes</button>
            </div>
        </form>

        <div class="records-section">
            <h2>Existing Attributes</h2>
            <table class="records-table">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Road Access</th>
                        <th>Travel Time</th>
                        <th>Distance</th>
                        <th>Transport</th>
                        <th>Signal</th>
                        <th>Hazard Zone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT a.*, b.name as barangay_name, s.name as sitio_name 
                             FROM barangay_sitio_attributes a 
                             LEFT JOIN barangays b ON a.barangay_id = b.id 
                             LEFT JOIN sitios s ON a.sitio_id = s.id 
                             ORDER BY b.name, s.name";
                    $attributes = $pdo->query($query)->fetchAll();
                    
                    foreach ($attributes as $attr):
                        $location = htmlspecialchars($attr['barangay_name']);
                        if ($attr['sitio_name']) {
                            $location .= ' - ' . htmlspecialchars($attr['sitio_name']);
                        }
                    ?>
                    <tr>
                        <td><?= $location ?></td>
                        <td><?= htmlspecialchars($attr['road_access']) ?></td>
                        <td><?= $attr['travel_time_to_market'] ?> mins</td>
                        <td><?= $attr['distance_km'] ?> km</td>
                        <td><?= htmlspecialchars($attr['public_transport']) ?></td>
                        <td><?= htmlspecialchars($attr['communication_signal']) ?></td>
                        <td><?= htmlspecialchars($attr['hazard_zone']) ?></td>
                        <td class="actions-cell">
                            <button onclick="editAttribute(<?= $attr['id'] ?>)" class="btn-icon" title="Edit">✏️</button>
                            <button onclick="deleteAttribute(<?= $attr['id'] ?>)" class="btn-icon" title="Delete">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <script>
        document.getElementById('attributesForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('mark_attributes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    window.location.reload();
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const barangaySelect = document.querySelector('select[name="barangay_id"]');
            const sitioSelect = document.getElementById('sitio_id');
            
            // Store all sitios in JavaScript
            const sitios = <?= json_encode($sitios) ?>;
            
            barangaySelect.addEventListener('change', function() {
                const selectedBarangayId = this.value;
                
                // Clear and disable sitio select if no barangay selected
                if (!selectedBarangayId) {
                    sitioSelect.innerHTML = '<option value="">-- Select Barangay First --</option>';
                    sitioSelect.disabled = true;
                    return;
                }
                
                // Convert selectedBarangayId to number for comparison
                const barangayIdNum = parseInt(selectedBarangayId, 10);
                
                // Filter sitios for selected barangay
                const filteredSitios = sitios.filter(s => parseInt(s.barangay_id, 10) === barangayIdNum);
                
                // Enable and populate sitio select
                sitioSelect.disabled = false;
                sitioSelect.innerHTML = '<option value="">-- Select Sitio --</option>' +
                    filteredSitios.map(s => 
                        `<option value="${s.id}">${s.name}</option>`
                    ).join('');
            });
        });

        function editAttribute(id) {
            if (confirm('Do you want to edit this attribute?')) {
                window.location.href = `edit_attribute.php?id=${id}`;
            }
        }

        function deleteAttribute(id) {
            if (confirm('Are you sure you want to delete this attribute?')) {
                fetch(`delete_attribute.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Attribute deleted successfully');
                            location.reload();
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error.message);
                    });
            }
        }
        </script>
    </div>
</body>
</html>
