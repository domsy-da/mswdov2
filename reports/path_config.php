<?php
include '../mswdo/includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_path = $_POST['report_save_path'];
    $stmt = $conn->prepare("UPDATE configurations SET config_value = ? WHERE config_key = 'report_save_path'");
    $stmt->execute([$new_path]);

    echo "<p style='color: green;'>Path updated successfully!</p>";
}

// Fetch current path
$stmt = $conn->prepare("SELECT config_value FROM configurations WHERE config_key = 'report_save_path'");
$stmt->execute();
$current_path = $stmt->fetchColumn();
?>

<form method="POST">
    <label for="report_save_path">Set Report Save Path:</label>
    <input type="text" name="report_save_path" id="report_save_path" value="<?= htmlspecialchars($current_path) ?>" required>
    <button type="submit">Update Path</button>
</form>
