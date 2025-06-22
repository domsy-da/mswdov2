<?php
require_once '../includes/db.php';
include '../includes/auth.php';
if (!isset($_SESSION['user_id'])) {
    // Store the requested URL for redirect after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    // Set toast message
    $_SESSION['toast_message'] = 'Please login to access this page';
    $_SESSION['toast_type'] = 'warning';
    
    // Redirect to main index page
    header('Location: /mswdov2/index.php');
    exit();
}

// Get unique clients from transactions
$query = "SELECT DISTINCT 
            t.client_name,
            t.client_complete_address,
            t.client_age,
            t.client_civil_status,
            t.beneficiary_id,
            COUNT(t.id) as transaction_count,
            MAX(t.request_date) as latest_transaction
          FROM transactions t
          GROUP BY t.client_name, t.client_complete_address, t.client_age, 
                   t.client_civil_status, t.beneficiary_id
          ORDER BY latest_transaction DESC";
$clients = $pdo->query($query)->fetchAll();

// Get unique barangays and sitios
$barangays_query = "SELECT DISTINCT client_barangay FROM transactions WHERE client_barangay IS NOT NULL ORDER BY client_barangay";
$sitios_query = "SELECT DISTINCT client_sitio FROM transactions WHERE client_sitio IS NOT NULL ORDER BY client_sitio";
$barangays = $pdo->query($barangays_query)->fetchAll(PDO::FETCH_COLUMN);
$sitios = $pdo->query($sitios_query)->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client History - MSWDO</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        
        <div class="filters">
            <input type="text" id="searchInput" placeholder="Search client name or address...">
            <select id="barangayFilter">
        <option value="">All Barangays</option>
        <?php foreach ($barangays as $barangay): ?>
            <option value="<?= htmlspecialchars($barangay) ?>"><?= htmlspecialchars($barangay) ?></option>
        <?php endforeach; ?>
    </select>
    <select id="sitioFilter">
        <option value="">All Sitios</option>
        <?php foreach ($sitios as $sitio): ?>
            <option value="<?= htmlspecialchars($sitio) ?>"><?= htmlspecialchars($sitio) ?></option>
        <?php endforeach; ?>
    </select>
        </div>

        <table id="clientsTable">
            <thead>
                <tr>
                    <th>Client Information</th>
                    <th>Age</th>
                    <th>Civil Status</th>
                    <th>Transaction Count</th>
                    <th>Latest Transaction</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr>
                    <td>
                        <div class="client-info">
                            <span class="client-name"><?= htmlspecialchars($client['client_name']) ?></span>
                            <span class="client-address"><?= htmlspecialchars($client['client_complete_address']) ?></span>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($client['client_age']) ?></td>
                    <td><?= htmlspecialchars($client['client_civil_status']) ?></td>
                    <td><span class="transaction-count"><?= $client['transaction_count'] ?></span></td>
                    <td>
                        <span class="latest-date">
                            <?= date('M d, Y', strtotime($client['latest_transaction'])) ?>
                        </span>
                    </td>
                    <td>
                        <a href="view.php?id=<?= $client['beneficiary_id'] ?>" 
                           class="action-btn" title="View Complete History">👁️</a>
                        <a href="../beneficiaries/view.php?id=<?= $client['beneficiary_id'] ?>" 
                           class="action-btn" title="View Complete History">📝</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div style="
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 120%;
        height: 120%;
        z-index: -1;
        opacity: 0.05;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    ">
        <img src="../assets/img/mswdologo2.jpg" alt="" style="
            width: 100%;
            height: 100%;
            object-fit: contain;
        ">
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const barangayFilter = document.getElementById('barangayFilter');
            const sitioFilter = document.getElementById('sitioFilter');
            const table = document.getElementById('clientsTable');
            const rows = table.getElementsByTagName('tr');

            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedBarangay = barangayFilter.value.toLowerCase();
                const selectedSitio = sitioFilter.value.toLowerCase();

                for (let i = 1; i < rows.length; i++) {
                    const row = rows[i];
                    const text = row.textContent.toLowerCase();
                    const address = row.querySelector('.client-address').textContent.toLowerCase();

                    const matchesSearch = text.includes(searchTerm);
                    const matchesBarangay = !selectedBarangay || address.includes(selectedBarangay);
                    const matchesSitio = !selectedSitio || address.includes(selectedSitio);

                    row.style.display = (matchesSearch && matchesBarangay && matchesSitio) ? '' : 'none';
                }
            }

            searchInput.addEventListener('input', filterTable);
            barangayFilter.addEventListener('change', filterTable);
            sitioFilter.addEventListener('change', filterTable);
        });
    </script>
</body>
</html>