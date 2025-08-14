<?php
// filepath: c:\xampp\htdocs\mswdov2\history\purpose_report.php

require_once '../includes/db.php';

// Fetch all transactions grouped by purpose
$purposes = ['Medical Expense', 'Burial', 'Educational'];
$purpose_data = [];

foreach ($purposes as $purpose) {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE LOWER(request_purpose) = LOWER(?) ORDER BY request_date DESC");
    $stmt->execute([$purpose]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;
    foreach ($rows as $row) {
        $total += floatval($row['amount']);
    }
    $purpose_data[$purpose] = [
        'transactions' => $rows,
        'total' => $total
    ];
}

// Senior Citizen transactions (age >= 60)
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE client_age >= 60 ORDER BY request_date DESC");
$stmt->execute();
$senior_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$senior_total = 0;
foreach ($senior_rows as $row) {
    $senior_total += floatval($row['amount']);
}

require_once '../documents/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (isset($_GET['pdf'])) {
    $html = '<h2 style="text-align:center;">Purpose Report</h2>';

    foreach ($purpose_data as $purpose => $data) {
        $html .= "<h3>$purpose Assistance</h3>";
        $html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%" style="border-collapse:collapse;font-size:13px;margin-bottom:10px;">
            <thead>
                <tr style="background:#eee;">
                    <th>Client Name</th>
                    <th>Age</th>
                    <th>Purpose</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Barangay</th>
                    <th>Sitio</th>
                </tr>
            </thead>
            <tbody>';
        foreach ($data['transactions'] as $t) {
            $html .= '<tr>
                <td>' . htmlspecialchars($t['client_name']) . '</td>
                <td>' . htmlspecialchars($t['client_age']) . '</td>
                <td>' . htmlspecialchars($t['purpose']) . '</td>
                <td>' . number_format($t['amount'], 2) . '</td>
                <td>' . htmlspecialchars($t['request_date']) . '</td>
                <td>' . htmlspecialchars($t['client_barangay']) . '</td>
                <td>' . htmlspecialchars($t['client_sitio']) . '</td>
            </tr>';
        }
        $html .= '</tbody></table>';
        $html .= '<div style="text-align:right;font-weight:bold;margin-bottom:30px;">Total: ' . number_format($data['total'], 2) . '</div>';
    }

    // Senior Citizen Section
    $html .= "<h3>Senior Citizen Transactions (Age 60+)</h3>";
    $html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%" style="border-collapse:collapse;font-size:13px;margin-bottom:10px;">
        <thead>
            <tr style="background:#eee;">
                <th>Client Name</th>
                <th>Age</th>
                <th>Purpose</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Barangay</th>
                <th>Sitio</th>
            </tr>
        </thead>
        <tbody>';
    foreach ($senior_rows as $t) {
        $html .= '<tr>
            <td>' . htmlspecialchars($t['client_name']) . '</td>
            <td>' . htmlspecialchars($t['client_age']) . '</td>
            <td>' . htmlspecialchars($t['purpose']) . '</td>
            <td>' . number_format($t['amount'], 2) . '</td>
            <td>' . htmlspecialchars($t['request_date']) . '</td>
            <td>' . htmlspecialchars($t['client_barangay']) . '</td>
            <td>' . htmlspecialchars($t['client_sitio']) . '</td>
        </tr>';
    }
    $html .= '</tbody></table>';
    $html .= '<div style="text-align:right;font-weight:bold;">Total for Senior Citizens: ' . number_format($senior_total, 2) . '</div>';

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream('purpose_report.pdf', ['Attachment' => false]); // false = open in browser
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purpose Report</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f7f7f9; color: #222; }
        .container { max-width: 1100px; margin: 30px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(60,60,60,0.08); padding: 32px 28px 24px 28px; }
        h2 { text-align: center; }
        h3 { margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #bbb; padding: 8px 10px; }
        th { background: #eee; }
        .total { text-align: right; font-weight: bold; margin-bottom: 30px; }
        .btn-pdf { background: #222; color: #fff; border: none; padding: 10px 22px; border-radius: 6px; cursor: pointer; font-size: 1rem; margin-bottom: 18px; }
        .btn-pdf:hover { background: #444; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <button onclick="window.history.back();" class="btn-pdf" style="background:#bbb; color:#222; margin-right: 12px;">&larr; Back</button>
            <h2 style="flex:1; text-align:center; margin:0;">Purpose Report</h2>
            <form method="post" action="purpose_report_pdf.php" target="_blank" id="all-pdf-form" style="margin:0;">
            <input type="hidden" name="html" value="">
            <button type="button" class="btn-pdf" onclick="printAllSections()">Generate PDF</button>
            </form>
        </div>

        <?php foreach ($purpose_data as $purpose => $data): ?>
            <h3><?= htmlspecialchars($purpose) ?> Assistance</h3>
            <form method="post" action="purpose_report_pdf.php" target="_blank" class="pdf-form">
                <input type="hidden" name="html" value="">
                <button type="button" class="btn-pdf" onclick="printSection(this)">Print PDF</button>
            </form>
            <div class="pdf-section">
                <table>
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Age</th>
                            <th>Purpose</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Barangay</th>
                            <th>Sitio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['transactions'] as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['client_name']) ?></td>
                                <td><?= htmlspecialchars($t['client_age']) ?></td>
                                <td><?= htmlspecialchars($t['request_purpose']) ?></td>
                                <td><?= number_format($t['amount'], 2) ?></td>
                                <td><?= htmlspecialchars($t['request_date']) ?></td>
                                <td><?= htmlspecialchars($t['client_barangay']) ?></td>
                                <td><?= htmlspecialchars($t['client_sitio']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="total">Total: <?= number_format($data['total'], 2) ?></div>
            </div>
        <?php endforeach; ?>

        <h3>Senior Citizen Transactions (Age 60+)</h3>
        <form method="post" action="purpose_report_pdf.php" target="_blank" class="pdf-form">
            <input type="hidden" name="html" value="">
            <button type="button" class="btn-pdf" onclick="printSection(this)">Print PDF</button>
        </form>
        <div class="pdf-section">
            <table>
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Age</th>
                        <th>Purpose</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Barangay</th>
                        <th>Sitio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($senior_rows as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['client_name']) ?></td>
                            <td><?= htmlspecialchars($t['client_age']) ?></td>
                            <td><?= htmlspecialchars($t['request_purpose']) ?></td>
                            <td><?= number_format($t['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($t['request_date']) ?></td>
                            <td><?= htmlspecialchars($t['client_barangay']) ?></td>
                            <td><?= htmlspecialchars($t['client_sitio']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="total">Total for Senior Citizens: <?= number_format($senior_total, 2) ?></div>
        </div>
    </div>

    <script>
    function printSection(btn) {
        var form = btn.closest('form');
        var section = form.nextElementSibling;
        var html = section.innerHTML;
        form.querySelector('input[name="html"]').value = html;
        form.submit();
    }

    function printAllSections() {
        var sections = document.querySelectorAll('.pdf-section');
        var html = '';
        sections.forEach(function(section) {
            html += section.innerHTML;
        });
        document.querySelector('#all-pdf-form input[name="html"]').value = html;
        document.getElementById('all-pdf-form').submit();
    }
    </script>
</body>
</html>