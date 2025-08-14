<?php
require '../documents/vendor/autoload.php';
include '../includes/db_connection.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    // Fetch demographic data
    $stmt = $conn->prepare("SELECT 
        SUM(CASE WHEN patient_age BETWEEN 0 AND 12 THEN 1 ELSE 0 END) AS children,
        SUM(CASE WHEN patient_age BETWEEN 13 AND 24 THEN 1 ELSE 0 END) AS youth,
        SUM(CASE WHEN patient_age BETWEEN 25 AND 59 THEN 1 ELSE 0 END) AS adults,
        SUM(CASE WHEN patient_age >= 60 THEN 1 ELSE 0 END) AS seniors,
        COUNT(DISTINCT beneficiary_id) AS total_patients
    FROM (
        SELECT DISTINCT beneficiary_id, patient_age
        FROM transactions
    ) AS unique_transactions");
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    $male_count = $conn->query("SELECT COUNT(*) FROM beneficiaries WHERE gender = 'Male'")->fetchColumn();
    $female_count = $conn->query("SELECT COUNT(*) FROM beneficiaries WHERE gender = 'Female'")->fetchColumn();

    $current_date = date('Y-m-d');
    $year = date('Y');

    if ($current_date >= "$year-01-01" && $current_date <= "$year-03-31") {
        $quarter = "First Quarter";
    } elseif ($current_date >= "$year-04-01" && $current_date <= "$year-06-30") {
        $quarter = "Second Quarter";
    } elseif ($current_date >= "$year-07-01" && $current_date <= "$year-09-30") {
        $quarter = "Third Quarter";
    } else {
        $quarter = "Fourth Quarter";
    }

    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) AS total_spent FROM transactions WHERE request_date BETWEEN ? AND ?");
    $stmt->execute(["$year-01-01", "$year-12-31"]);
    $total_spent = $stmt->fetch(PDO::FETCH_ASSOC)['total_spent'];

    // Top Services Requested
    $stmt = $conn->prepare("
        SELECT request_purpose, COUNT(*) as count 
        FROM transactions 
        GROUP BY request_purpose 
        ORDER BY count DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $top_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $top_services_text = implode(', ', array_map(function($row) {
        return $row['request_purpose'] . ' (' . $row['count'] . ')';
    }, $top_services));

    // Top Barangays from beneficiaries
    $stmt = $conn->prepare("
        SELECT 
            barangay,
            COUNT(*) AS count 
        FROM beneficiaries 
        GROUP BY barangay 
        ORDER BY count DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $top_barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $top_barangays_text = implode(', ', array_map(function($row) {
        return $row['barangay'] . ' (' . $row['count'] . ')';
    }, $top_barangays));

    // Average Assistance per Individual
    $average_amount = $data['total_patients'] > 0 ? $total_spent / $data['total_patients'] : 0;

    // Top Barangay Served by Volume (from transactions)
    $stmt = $conn->prepare("
        SELECT 
            b.barangay,
            COUNT(*) AS count 
        FROM transactions t
        INNER JOIN beneficiaries b ON t.beneficiary_id = b.id
        GROUP BY b.barangay 
        ORDER BY count DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $top_barangay_service = $stmt->fetch(PDO::FETCH_ASSOC);

    // Top Sitio Served by Volume (from transactions)
    $stmt = $conn->prepare("
        SELECT 
            b.sitio,
            COUNT(*) AS count 
        FROM transactions t
        INNER JOIN beneficiaries b ON t.beneficiary_id = b.id
        GROUP BY b.sitio 
        ORDER BY count DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $top_sitio_service = $stmt->fetch(PDO::FETCH_ASSOC);

    // Generate PDF report
    $html = '
<html>
<head>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.3; }
        h1, h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #f2f2f2; }
        .section-title { margin-top: 30px; font-size: 1.1em; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Quarterly Report - ' . htmlspecialchars($quarter) . ' ' . htmlspecialchars($year) . '</h1>
    <p><strong>Organization:</strong> Municipal Social Welfare and Development Office</p>
    <p><strong>Total Expense:</strong> ₱' . number_format($total_spent, 2) . '</p>
    <p><strong>Total Served:</strong> ' . $data['total_patients'] . '</p>
    <p><strong>Total Number of Individuals:</strong> ' . ($male_count + $female_count) . '</p>

    <h2>Summary of Service Trend</h2>
    <table>
        <tr>
            <th>Most Requested Services</th>
            <th>Barangays with Most Beneficiaries</th>
            <th>Average Assistance per Individual</th>
        </tr>
        <tr>
            <td>' . htmlspecialchars($top_services_text) . '</td>
            <td>' . htmlspecialchars($top_barangays_text) . '</td>
            <td>₱' . number_format($average_amount, 2) . '</td>
        </tr>
    </table>

    <h2>Demographic Breakdown & Key Service and Geographic Insights</h2>
    <table>
        <tr>
            <th>Male</th>
            <th>Female</th>
            <th>Top Barangay Served</th>
            <th>Top Sitio Served</th>
        </tr>
        <tr>
            <td>' . $male_count . '</td>
            <td>' . $female_count . '</td>
            <td>' . htmlspecialchars($top_barangay_service ? $top_barangay_service["barangay"] . " (" . $top_barangay_service["count"] . ")" : "N/A") . '</td>
            <td>' . htmlspecialchars($top_sitio_service ? $top_sitio_service["sitio"] . " (" . $top_sitio_service["count"] . ")" : "N/A") . '</td>
        </tr>
    </table>

    <h2>Age Group</h2>
    <table>
        <tr>
            <th>Child</th>
            <th>Youth</th>
            <th>Adult</th>
            <th>Senior</th>
        </tr>
        <tr>
            <td>' . $data['children'] . '</td>
            <td>' . $data['youth'] . '</td>
            <td>' . $data['adults'] . '</td>
            <td>' . $data['seniors'] . '</td>
        </tr>
    </table>
</body>
</html>
';

    $options = new Options();
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->set_option('defaultFont', 'Helvetica');
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('Quarterly_Report_' . $year . '.pdf', ['Attachment' => false]);
    exit;
} catch (Exception $e) {
    die("<p style='color: red;'>Error generating report: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>");
}
?>
