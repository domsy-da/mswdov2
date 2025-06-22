<?php
require_once '../../includes/db.php'; // Assumes $pdo is your PDO connection

// Handle delete single
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM combined_transactions WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: index.php");
    exit;
}

// Handle delete all
if (isset($_GET['delete_all'])) {
    $pdo->exec("DELETE FROM combined_transactions");
    header("Location: index.php");
    exit;
}

// Fetch data
$stmt = $pdo->query("SELECT * FROM combined_transactions ORDER BY id DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Combined Transactions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
</head>
<body>
<div class="container">
    <h1>Combined Transactions</h1>
    <div class="actions">
        <button class="btn" onclick="window.location.href='../'">🔙Back</button>
        <form method="get" style="display:inline;">
            <button class="btn btn-danger" type="submit" name="delete_all" value="1" onclick="return confirm('Delete all transactions?')">Delete All</button>
        </form>
        <input type="text" class="search-box" id="searchInput" placeholder="Search...">
    </div>
    <div class="table-container">
        <table id="transactionsTable">
            <thead>
                <tr>
                    <?php if (!empty($rows)): ?>
                        <?php foreach (array_keys($rows[0]) as $col): ?>
                            <th><?= htmlspecialchars($col) ?></th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="100" style="text-align:center;">No transactions found.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($row as $cell): ?>
                                <td><?= htmlspecialchars($cell) ?></td>
                            <?php endforeach; ?>
                            <td>
                                <div class="action-btns">
                                    <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete this transaction?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#transactionsTable tbody tr');
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>
</body>
</html>
