<?php
require 'vendor/autoload.php';
include '../includes/db_connection.php';

use PhpOffice\PhpWord\TemplateProcessor;

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

    $template_file = 'quarterReport.docx';
    if (!file_exists($template_file)) {
        die("<p style='color: red;'>Error: Template file is missing.</p>");
    }

    $template = new TemplateProcessor($template_file);

    // Set static values
    $template->setValue('OrganizationName', 'Municipal Social Welfare and Development Office');
    $template->setValue('Quarter', $quarter);
    $template->setValue('Year', $year);
    $template->setValue('TotalExpenseAmount', '₱' . number_format($total_spent, 2));
    $template->setValue('NumberOfMales', $male_count);
    $template->setValue('NumberOfFemales', $female_count);
    $template->setValue('NumberOfChildren', $data['children']);
    $template->setValue('NumberOfYouth', $data['youth']);
    $template->setValue('NumberOfAdults', $data['adults']);
    $template->setValue('NumberOfSeniors', $data['seniors']);
    $template->setValue('totalofserved', $data['total_patients']);
    $template->setValue('TotalNumberOfIndividuals', $male_count + $female_count);

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
    $template->setValue('TopServices', $top_services_text);

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
    $template->setValue('TopBarangays', $top_barangays_text);

    // Average Assistance per Individual
    $average_amount = $data['total_patients'] > 0 ? $total_spent / $data['total_patients'] : 0;
    $template->setValue('AverageAmount', '₱' . number_format($average_amount, 2));

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
    $template->setValue('TopBarangaysByService', $top_barangay_service ? $top_barangay_service['barangay'] . ' (' . $top_barangay_service['count'] . ')' : 'N/A');

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
    $template->setValue('TopSitiosByService', $top_sitio_service ? $top_sitio_service['sitio'] . ' (' . $top_sitio_service['count'] . ')' : 'N/A');

    // Save report
    $stmt = $conn->prepare("SELECT config_value FROM configurations WHERE config_key = 'report_save_path'");
    $stmt->execute();
    $output_dir = $stmt->fetchColumn() ?: 'reports/';

    if (!is_dir($output_dir)) {
        mkdir($output_dir, 0755, true);
    }

    $output_file = rtrim($output_dir, '/') . '/Quarterly_Report_' . $year . '_' . time() . '.docx';

    $log_stmt = $conn->prepare("INSERT INTO activity_logs (act_name, act_type) VALUES (?, ?)");
    $log_stmt->execute(["Generated {$quarter} Report for {$year}", 'report_generation']);

    try {
        $template->saveAs($output_file);
        echo "
            <div style='text-align: center; font-family: Arial, sans-serif; margin-top: 50px;'>
                <p style='color: green; font-size: 18px;'>Report saved successfully at <strong>" . htmlspecialchars($output_file, ENT_QUOTES, 'UTF-8') . "</strong></p>
                <a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go Back</a>
            </div>
        ";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error saving report: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
    }

    // Optional: move and delete transactions
    if (isset($_POST['delete_transactions']) && $_POST['delete_transactions'] == '1') {
        try {
            $stmt = $conn->prepare("INSERT INTO combined_transactions (
                    beneficiary_id, patient_name, client_name, request_type, 
                    request_purpose, request_date, amount, diagnosis_school, id_type, person_signed, created_at
                )
                SELECT 
                    t.beneficiary_id, t.patient_name, t.client_name, t.request_type, 
                    t.request_purpose, t.request_date, t.amount, t.diagnosis_school, a.id_type, t.prep_by, t.created_at
                FROM transactions t
                INNER JOIN attached_ids a ON t.id = a.transaction_id
            ");
            $stmt->execute();

            $stmt = $conn->prepare("DELETE FROM transactions");
            $stmt->execute();

            echo "<p style='color: green;'>Transactions moved and deleted successfully.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
        }
    }

    exit;
} catch (Exception $e) {
    die("<p style='color: red;'>Error generating report: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>");
}
?>
