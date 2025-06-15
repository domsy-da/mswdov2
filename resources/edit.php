<?php
include '../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE resources SET name = ?, description = ?, quantity = ? WHERE id = ?");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['quantity'],
        $id
    ]);
    header('Location: index.php');
    exit;
}

// Fetch resource
$stmt = $pdo->prepare("SELECT * FROM resources WHERE id = ?");
$stmt->execute([$id]);
$resource = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resource) {
    die('Resource not found.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Resource</title>
    <link rel="stylesheet" href="../assets/css/beneficiary.css" />
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1>Edit Resource</h1>

            <form method="POST" class="form">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($resource['name']) ?>" required />
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="description"><?= htmlspecialchars($resource['description']) ?></textarea>
                </div>
                <div class="form-group">
                    <label>Quantity:</label>
                    <input type="number" name="quantity" value="<?= htmlspecialchars($resource['quantity']) ?>" min="0" required />
                </div>
                <button type="submit" class="btn btn-primary">Update Resource</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </main>
</body>
</html>
