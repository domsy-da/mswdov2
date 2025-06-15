<?php
include '../../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_path = $_POST['report_save_path'];
    $document_path = $_POST['document_save_path'];

    // Update the report save path
    $stmt = $conn->prepare("UPDATE configurations SET config_value = ? WHERE config_key = 'report_save_path'");
    $stmt->execute([$report_path]);

    // Update the document save path
    $stmt = $conn->prepare("UPDATE configurations SET config_value = ? WHERE config_key = 'document_save_path'");
    $stmt->execute([$document_path]);

    $success_message = "Paths updated successfully!";
}

// Fetch current paths
$stmt = $conn->prepare("SELECT config_value FROM configurations WHERE config_key = 'report_save_path'");
$stmt->execute();
$report_path = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT config_value FROM configurations WHERE config_key = 'document_save_path'");
$stmt->execute();
$document_path = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        .admin-paths {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }

        .back-button {
            background: #f5f5f5;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .back-button:hover {
            background: #e0e0e0;
        }

        .help-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .help-button:hover {
            background: #0056b3;
        }

        .instructions {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: none;
        }

        .instructions h3 {
            margin-top: 0;
            color: #333;
        }

        .instructions ol {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }

        .form-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: #333;
        }

        .form-group input {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }

        .submit-button {
            background: #28a745;
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .submit-button:hover {
            background: #218838;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="admin-paths">
        <div class="page-header">
            <h1>Path Management</h1>
            <a href="../index.php" class="back-button">
                ← Back to Admin Panel
            </a>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <div style="text-align: right; margin-bottom: 15px;">
            <button class="help-button" id="helpBtn">
                Show Instructions
            </button>
        </div>

        <div id="instructions" class="instructions" style="display: none;">
            <h3>Instructions</h3>
            <p>Follow these steps to update the paths:</p>
            <ol>
                <li>Open your file explorer and navigate to the directory where you want to save the reports or documents.</li>
                <li>Right-click on the directory and select "Properties" or "Get Info" depending on your operating system.</li>
                <li>Copy the full path from the "Location" or "Path" field. Or just copy the path in the url bar.</li>
                <li>Paste the copied path into the "Report Save Path" or "Document Save Path" field in the form below.</li>
                <li>Click the "Update Paths" button to save the changes.</li>
            </ol>
        </div>

        <form method="POST" class="form-container">
            <div class="form-group">
                <label for="report_save_path">Report Save Path:</label>
                <input type="text" name="report_save_path" id="report_save_path" value="<?= htmlspecialchars($report_path) ?>" required>
            </div>

            <div class="form-group">
                <label for="document_save_path">Document Save Path:</label>
                <input type="text" name="document_save_path" id="document_save_path" value="<?= htmlspecialchars($document_path) ?>" required>
            </div>

            <button type="submit" class="submit-button">Update Paths</button>
        </form>
    </div>

    <script>
        document.getElementById('helpBtn').addEventListener('click', function() {
            const instructions = document.getElementById('instructions');
            const isHidden = instructions.style.display === 'none';
            instructions.style.display = isHidden ? 'block' : 'none';
            this.textContent = isHidden ? 'Hide Instructions' : 'Show Instructions';
        });
    </script>
</body>
</html>
