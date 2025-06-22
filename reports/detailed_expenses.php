<?php
// Database connection
include '../includes/db_connection.php';

// Query to get all barangays for dropdown
$barangay_query = "SELECT name FROM barangays ORDER BY name";
$barangay_stmt = $conn->prepare($barangay_query);
$barangay_stmt->execute();

// Query to get all expenses
$query = "SELECT 
    beneficiary_id,
    client_name,
    patient_sitio,
    patient_barangay,
    SUM(amount) as total_amount
    FROM transactions
    GROUP BY beneficiary_id, client_name, patient_sitio, patient_barangay
    ORDER BY beneficiary_id";

$stmt = $conn->prepare($query);
$stmt->execute();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detailed Expenses Report</title>
    <link rel="stylesheet" href="css/det_expen.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn btn-ys">Go back</a>
        <h2>Detailed Expenses Report</h2>
        
        <!-- Filter Form -->
        <div class="filter-form">
            <select id="barangayFilter" onchange="filterTable()">
                <option value="">All Barangays</option>
                <?php while($barangay = $barangay_stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                    <option value="<?php echo htmlspecialchars($barangay['name']); ?>">
                        <?php echo htmlspecialchars($barangay['name']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <table id="expensesTable">
            <thead>
                <tr>
                    <th>Beneficiary ID</th>
                    <th>Client Name</th>
                    <th>Sitio</th>
                    <th>Barangay</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grand_total = 0;
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { 
                    $grand_total += $row['total_amount'];
                ?>
                <tr class="expense-row" data-barangay="<?php echo htmlspecialchars($row['patient_barangay']); ?>">
                    <td><?php echo htmlspecialchars($row['beneficiary_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['patient_sitio']); ?></td>
                    <td><?php echo htmlspecialchars($row['patient_barangay']); ?></td>
                    <td data-amount="<?php echo $row['total_amount']; ?>">
                        ₱<?php echo number_format($row['total_amount'], 2); ?>
                    </td>
                </tr>
                <?php } ?>
                <tr id="totalRow" class="total">
                    <td colspan="4">Grand Total</td>
                    <td id="grandTotal">₱<?php echo number_format($grand_total, 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
    function filterTable() {
        const selectedBarangay = document.getElementById('barangayFilter').value;
        const rows = document.getElementsByClassName('expense-row');
        let filteredTotal = 0;

        for (let row of rows) {
            const barangay = row.getAttribute('data-barangay');
            if (!selectedBarangay || barangay === selectedBarangay) {
                row.classList.remove('hidden');
                filteredTotal += parseFloat(row.querySelector('[data-amount]').getAttribute('data-amount'));
            } else {
                row.classList.add('hidden');
            }
        }

        // Update grand total
        document.getElementById('grandTotal').textContent = 
            '₱' + filteredTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    </script>
</body>
</html>