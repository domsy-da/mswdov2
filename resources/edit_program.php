<?php
require_once '../includes/db.php';
require_once '../includes/auth_check.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch program data
$stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
$stmt->execute([$id]);
$program = $stmt->fetch();

if (!$program) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Program - MSWDO</title>
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-title {
            margin: 0 0 20px 0;
            color: #333;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 97%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-group textarea {
            min-height: 100px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: #333;
            color: white;
        }

        .btn-secondary {
            background: #666;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>

    <div class="container">
        <form id="editProgramForm" class="form">
            <h2 class="form-title">Edit Program</h2>
            
            <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="program_name">Program Name:</label>
                    <input type="text" 
                           id="program_name" 
                           name="program_name" 
                           value="<?= htmlspecialchars($program['program_name']) ?>" 
                           required />
                </div>
                
                <div class="form-group">
                    <label for="program_type">Program Type:</label>
                    <select id="program_type" name="program_type" required>
                        <option value="">Select Type</option>
                        <option value="Educational" <?= $program['program_type'] === 'Educational' ? 'selected' : '' ?>>
                            Educational
                        </option>
                        <option value="Medical" <?= $program['program_type'] === 'Medical' ? 'selected' : '' ?>>
                            Medical
                        </option>
                        <option value="Financial" <?= $program['program_type'] === 'Financial' ? 'selected' : '' ?>>
                            Financial
                        </option>
                        <option value="Social" <?= $program['program_type'] === 'Social' ? 'selected' : '' ?>>
                            Social
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="target_beneficiaries">Target Beneficiaries:</label>
                    <input type="number" 
                           id="target_beneficiaries" 
                           name="target_beneficiaries" 
                           value="<?= htmlspecialchars($program['target_beneficiaries']) ?>" 
                           min="1" 
                           required />
                </div>
            </div>
            
            <div class="form-group">
                <label for="program_description">Description:</label>
                <textarea id="program_description" 
                          name="program_description"><?= htmlspecialchars($program['program_description']) ?></textarea>
            </div>
            
            <div class="actions">
                <a href="index.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Program</button>
            </div>
        </form>
    </div>

    <script>
    document.getElementById('editProgramForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('update_program.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Program updated successfully');
                window.location.href = 'index.php';
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    });
    </script>
</body>
</html>