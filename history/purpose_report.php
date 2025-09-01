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
        :root {
            --primary: #2563eb;
            --secondary: #64748b;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --surface: #ffffff;
            --background: #f8fafc;
        }

        body { 
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--background);
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 2rem;
        }

        .container { 
            max-width: 1200px; 
            margin: 0 auto;
            background: var(--surface);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 2rem;
        }

        .page-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .filter-bar {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-bar select,
        .filter-bar input {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            min-width: 160px;
            font-size: 0.875rem;
            color: #1e293b;
            transition: all 0.2s;
        }

        .filter-bar select:hover,
        .filter-bar input:hover {
            border-color: var(--primary);
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-secondary {
            background: var(--secondary);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .section-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .section-header {
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-body {
            padding: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        th {
            background: #f8fafc;
            font-weight: 500;
            color: #64748b;
            font-size: 0.875rem;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .total {
            text-align: right;
            padding: 1rem 1.5rem;
            font-weight: 500;
            color: var(--primary);
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <button onclick="window.history.back();" class="btn btn-secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </button>
            <h2 style="margin:0">Purpose Report</h2>
            <form method="post" action="purpose_report_pdf.php" target="_blank" id="all-pdf-form" style="margin-left:auto">
                <input type="hidden" name="html" value="">
                <button type="button" class="btn btn-primary" onclick="printAllSections()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Generate PDF
                </button>
            </form>
        </div>

        <div class="filter-bar">
            <div class="filter-group">
                <select id="barangayFilter">
                    <option value="">All Barangays</option>
                    <?php foreach ($barangays as $barangay): 
                        if ($barangay): ?>
                            <option value="<?= htmlspecialchars($barangay) ?>">
                                <?= htmlspecialchars($barangay) ?>
                            </option>
                    <?php endif; endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <select id="purposeFilter">
                    <option value="">All Purposes</option>
                    <?php foreach ($purposes as $purpose): ?>
                        <option value="<?= htmlspecialchars($purpose) ?>">
                            <?= htmlspecialchars($purpose) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <input type="date" id="startDate" placeholder="Start Date">
            </div>

            <button type="button" id="resetFilters" class="btn btn-secondary">
                Reset Filters
            </button>
        </div>

        <?php foreach ($purpose_data as $purpose => $data): ?>
            <div class="section-card">
                <div class="section-header">
                    <h3 style="margin:0"><?= htmlspecialchars($purpose) ?> Assistance</h3>
                    <form method="post" action="purpose_report_pdf.php" target="_blank" class="pdf-form" style="margin:0">
                        <input type="hidden" name="html" value="">
                        <button type="button" class="btn btn-secondary" onclick="printSection(this)">
                            Print PDF
                        </button>
                    </form>
                </div>
                <div class="section-body">
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
                </div>
                <div class="total">
                    Total: <?= number_format($data['total'], 2) ?>
                </div>
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
        document.addEventListener('DOMContentLoaded', function() {
    const barangayFilter = document.getElementById('barangayFilter');
    const purposeFilter = document.getElementById('purposeFilter');
    const startDate = document.getElementById('startDate');
    const resetFilters = document.getElementById('resetFilters');
    const tables = document.querySelectorAll('table');

    function filterTables() {
        const selectedBarangay = barangayFilter.value;
        const selectedPurpose = purposeFilter.value;
        const selectedDate = startDate.value;

        tables.forEach(table => {
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const barangay = row.cells[5].textContent.trim();
                const purpose = row.cells[2].textContent.trim();
                const date = row.cells[4].textContent.trim();
                
                const barangayMatch = !selectedBarangay || barangay === selectedBarangay;
                const purposeMatch = !selectedPurpose || purpose === selectedPurpose;
                const dateMatch = !selectedDate || new Date(date) >= new Date(selectedDate);

                row.style.display = (barangayMatch && purposeMatch && dateMatch) ? '' : 'none';
            });

            // Update totals for each section
            const visibleAmounts = Array.from(rows)
                .filter(row => row.style.display !== 'none')
                .map(row => parseFloat(row.cells[3].textContent.replace(/[^0-9.-]+/g, '')));

            const total = visibleAmounts.reduce((sum, amount) => sum + amount, 0);
            
            const totalDiv = table.closest('.pdf-section').querySelector('.total');
            if (totalDiv) {
                const label = totalDiv.textContent.includes('Senior Citizens') ? 'Total for Senior Citizens: ' : 'Total: ';
                totalDiv.textContent = label + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        });
    }

    barangayFilter.addEventListener('change', filterTables);
    purposeFilter.addEventListener('change', filterTables);
    startDate.addEventListener('change', filterTables);
    
    resetFilters.addEventListener('click', function() {
        barangayFilter.value = '';
        purposeFilter.value = '';
        startDate.value = '';
        filterTables();
    });
});
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