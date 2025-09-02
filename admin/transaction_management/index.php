<?php
require_once '../../includes/db.php';

// Fetch all transactions with patient/client info
$stmt = $pdo->query("SELECT id, beneficiary_id, client_name, client_age, amount, client_sitio, client_barangay FROM transactions");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Transaction Management</title>
    <style>
        body {
            background: #f7f7f9;
            color: #222;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 93%;
            margin: 10px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(60,60,60,0.08);
            padding: 32px 28px 24px 28px;
        }
        h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 18px;
            color: #222;
        }
        .btn-back {
            background: #444;
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 6px;
            margin-bottom: 18px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .btn-back:hover {
            background: #222;
        }
        #search {
            width: 320px;
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #bbb;
            border-radius: 6px;
            font-size: 1rem;
            background: #f2f2f2;
            color: #222;
            outline: none;
            transition: border 0.2s;
        }
        #search:focus {
            border: 1.5px solid #888;
            background: #fff;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 10px;
            background: #fafbfc;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(80,80,80,0.04);
        }
        th, td {
            padding: 14px 12px;
            text-align: left;
        }
        th {
            background: #333;
            color: #fff;
            font-weight: 500;
            font-size: 1rem;
            border-bottom: 2px solid #444;
        }
        tr {
            transition: background 0.18s;
        }
        tr:nth-child(even) {
            background: #f1f1f3;
        }
        tr:nth-child(odd) {
            background: #e9e9ed;
        }
        tr:hover {
            background: #d6d6db;
        }
        .btn-delete {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 7px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.98rem;
            transition: background 0.2s;
        }
        .btn-delete:hover {
            background: #b93a27;
        }
        @media (max-width: 800px) {
            .container { padding: 10px 2vw; }
            table, th, td { font-size: 0.95rem; }
            #search { width: 100%; }
        }
    </style>
    <script>
        function filterTable() {
            let input = document.getElementById('search').value.toLowerCase();
            let rows = document.querySelectorAll('#transactionsTable tbody tr');
            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        }
        function confirmDelete(id) {
            if(confirm('Are you sure you want to delete this transaction?')) {
                window.location.href = 'delete.php?id=' + id;
            }
        }
        function goBack() {
            window.history.back();
        }
        function archiveAll() {
            if(confirm('Are you sure you want to move all transactions to archive?')) {
                window.location.href = 'archive_all.php';
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <button class="btn-back" onclick="goBack()">Back</button>
        <h1>Transaction Management</h1>
        <button class="btn-delete" onclick="archiveAll()">Archive All</button>
        <input type="text" id="search" onkeyup="filterTable()" placeholder="Search transactions...">
        <table id="transactionsTable">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Beneficiary ID</th>
                    <th>Client Name</th>
                    <th>Age</th>
                    <th>Amount</th>
                    <th>Sitio</th>
                    <th>Barangay</th>
                    <th>ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($transactions as $row): ?>
                <tr>
                    <td>
                        <button class="btn-delete" onclick="confirmDelete(<?= $row['id'] ?>)">Delete</button>
                    </td>
                    <td><?= htmlspecialchars($row['beneficiary_id']) ?></td>
                    <td><?= htmlspecialchars($row['client_name']) ?></td>
                    <td><?= htmlspecialchars($row['client_age']) ?></td>
                    <td><?= htmlspecialchars($row['amount']) ?></td>
                    <td><?= htmlspecialchars($row['client_sitio']) ?></td>
                    <td><?= htmlspecialchars($row['client_barangay']) ?></td>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>