<?php
require_once 'vendor/autoload.php';
require_once '../includes/db_connection.php';

use PhpOffice\PhpWord\TemplateProcessor;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Fetch document_save_path from the configuration table
        $sql_config = "SELECT config_value FROM configurations WHERE config_key = 'document_save_path'";
        $stmt_config = $conn->prepare($sql_config);
        $stmt_config->execute();
        $config = $stmt_config->fetch(PDO::FETCH_ASSOC);
        $document_save_path = $config['config_value'];

        $request_purpose = $_POST['request_purpose'];

        // Determine the template folder and short code based on request_purpose
        $template_folder = '';
        $short_code = '';
        switch ($request_purpose) {
            case 'educational':
                $template_folder = 'temp_educational';
                $short_code = 'el';
                break;
            case 'burial':
                $template_folder = 'temp_burial';
                $short_code = 'bl';
                break;
            case 'med_exp':
                $template_folder = 'temp_medical_expenses';
                $short_code = 'me';
                break;
            case 'med_exp_hel':
                $template_folder = 'temp_medical_expenses_hel';
                $short_code = 'meh';
                break;
            case 'hos_bill':
                $template_folder = 'temp_hos_bill';
                $short_code = 'hb';
                break;
            case 'hos_bill_hel':
                $template_folder = 'temp_hos_bill_hel';
                $short_code = 'hbh';
                break;
            default:
                throw new Exception("Invalid request purpose.");
        }

        $request_type = $_POST['request_type'];
        $beneficiary_id = $_POST['id'];
        $patient_gender = strtolower($_POST['patient_gender']);
        $client_gender = strtolower($_POST['client_gender']); 
        $cle_gen = ($client_gender == 'male') ? 'He' : 'She';
        $eli_med_gen = ($client_gender == 'male') ? 'his' : 'her';
        $relationship = strtolower($_POST['relation']); 

        if ($request_type == 'client_patient') {
            $relation = $cle_gen;
            $rel_med_eli = $eli_med_gen;
        } else {
            if ($client_gender == 'male') {
                $relation = "His " . $relationship;
            } else {
                $relation = "Her " . $relationship;
            }
        }

        // Define the save directory using the fetched path
        $saveDirectory = rtrim($document_save_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'request_' . $beneficiary_id . DIRECTORY_SEPARATOR;
        if (!file_exists($saveDirectory)) {
            mkdir($saveDirectory, 0777, true);
        }

        // Fetch relatives of the beneficiary
        $sql_relatives = "SELECT name, age, civil_status, relationship, educational_attainment, occupation FROM relatives WHERE beneficiary_id = ?";
        $stmt_relatives = $conn->prepare($sql_relatives);
        $stmt_relatives->execute([$beneficiary_id]);
        $relatives = $stmt_relatives->fetchAll(PDO::FETCH_ASSOC);

        // Define maximum placeholders
        $max_relatives = 5;

        // Load the template documents from the determined folder
        $templateProcessor = new TemplateProcessor($template_folder . '/socialcase.docx');
        
        // Get and set all form data
        $formFields = [
            // Request Type
            'request_type' => $_POST['request_type'],
            
            // Patient Information
            'patient_name' => $_POST['patient_name'],
            'patient_age' => $_POST['patient_age'],
            'patient_gender' => $_POST['patient_gender'],
            'patient_civil_status' => $_POST['patient_civil_status'],
            'patient_birthday' => $_POST['patient_birthday'],
            'patient_birthplace' => $_POST['patient_birthplace'],
            'patient_education' => $_POST['patient_education'],
            'patient_occupation' => $_POST['patient_occupation'],
            'patient_religion' => $_POST['patient_religion'],
            // Updated address fields
            'patient_sitio' => $_POST['patient_sitio'],
            'patient_barangay' => $_POST['patient_barangay'],
            'patient_complete_address' => $_POST['patient_complete_address'],
            
            // Client Information
            'client_name' => $_POST['client_name'],
            'client_age' => $_POST['client_age'],
            'client_gender' => $_POST['client_gender'],
            'client_civil_status' => $_POST['client_civil_status'],
            'client_birthday' => $_POST['client_birthday'],
            'client_birthplace' => $_POST['client_birthplace'],
            'client_education' => $_POST['client_education'],
            'client_occupation' => $_POST['client_occupation'],
            'client_religion' => $_POST['client_religion'],
            // Updated address fields
            'client_sitio' => $_POST['client_sitio'],
            'client_barangay' => $_POST['client_barangay'],
            'client_complete_address' => $_POST['client_complete_address'],
            
            // Additional Information
            'request_date' => $_POST['request_date'],
            'amount' => $_POST['amount'],
            'diagnosis_school' => $_POST['diagnosis_school'],
            'id_type' => $_POST['id_type'],
            'prep_by' => $_POST['prep_by'],
            'pos_prep' => $_POST['pos_prep'],
            'not_by' => $_POST['not_by'],
            'pos_not' => $_POST['pos_not'],
            'he_she' => $relation
        ];

        // Replace all placeholders in the template
        foreach ($formFields as $field => $value) {
            $templateProcessor->setValue($field, $value);
        }

        for ($i = 0; $i < $max_relatives; $i++) {
            $index = $i + 1; // Placeholder starts from 1
            $templateProcessor->setValue("rel_name#$index", isset($relatives[$i]) ? $relatives[$i]['name'] : '');
            $templateProcessor->setValue("rel_age #$index", isset($relatives[$i]) ? $relatives[$i]['age'] : '');
            $templateProcessor->setValue("rel_civ #$index", isset($relatives[$i]) ? $relatives[$i]['civil_status'] : '');
            $templateProcessor->setValue("rel_relation #$index", isset($relatives[$i]) ? $relatives[$i]['relationship'] : '');
            $templateProcessor->setValue("rel_educ #$index", isset($relatives[$i]) ? $relatives[$i]['educational_attainment'] : '');
            $templateProcessor->setValue("rel_occ #$index", isset($relatives[$i]) ? $relatives[$i]['occupation'] : '');
        }

        // Format dates if needed
        $formattedRequestDate = date('F d, Y', strtotime($_POST['request_date']));
        $formattedPatientBday = date('F d, Y', strtotime($_POST['patient_birthday']));
        $formattedClientBday = date('F d, Y', strtotime($_POST['client_birthday']));
        
        $templateProcessor->setValue('formatted_request_date', $formattedRequestDate);
        $templateProcessor->setValue('formatted_patient_birthday', $formattedPatientBday);
        $templateProcessor->setValue('formatted_client_birthday', $formattedClientBday);
        
        // Generate new filename using patient name, short code, and timestamp
        $clientName = preg_replace('/[^A-Za-z0-9]/', '_', $_POST['client_name']);
        $newFileName = 'request_' . $short_code . '_' . $clientName . '_' . date('Y-m-d_H-i-s') . '.docx';
        $fullPath = $saveDirectory . $newFileName;
        
        // Save the document
        $templateProcessor->saveAs($fullPath);

        // Process Certificate of Eligibility template
        $certTemplate = new TemplateProcessor($template_folder . '/cert_of_eligib.docx');
        $certTemplate->setValue('client_name', $_POST['client_name']);
        $certTemplate->setValue('client_address', $_POST['client_complete_address']); // Updated to use complete address
        $certTemplate->setValue('prep_by', $_POST['prep_by']);
        $certTemplate->setValue('brot_sis',  ($request_type == "client_patient") ? $rel_med_eli : $relation);
        $certFileName = 'cert_eligib_' . $short_code . '_' . preg_replace('/[^A-Za-z0-9]/', '_', $_POST['client_name']) . '_' . date('Y-m-d_H-i-s') . '.docx';
        $certFilePath = $saveDirectory . $certFileName;
        $certTemplate->saveAs($certFilePath);

        // Process Medical Healthcard template
        $medTemplate = new TemplateProcessor($template_folder . '/med_hel.docx');
        $medTemplate->setValue('client_name', $_POST['client_name']);
        $medTemplate->setValue('client_civil_status', $_POST['client_civil_status']);
        $medTemplate->setValue('client_address', $_POST['client_complete_address']); // Updated to use complete address
        $medTemplate->setValue('request_date', $formattedRequestDate);
        $medTemplate->setValue('prep_by', $_POST['prep_by']);
        $medTemplate->setValue('brot_sis', ($request_type == "client_patient") ? $rel_med_eli : $relation);
        $medFileName = 'med_hel_' . $short_code . '_' . preg_replace('/[^A-Za-z0-9]/', '_', $_POST['client_name']) . '_' . date('Y-m-d_H-i-s') . '.docx';
        $medFilePath = $saveDirectory . $medFileName;
        $medTemplate->saveAs($medFilePath);

            
        // Show success message with styling
        // Database insertion code
        try {
            // Start transaction
            $conn->beginTransaction();
            $request_purpose = $_POST['request_purpose'];
            function str_request_purpose($request_purpose) {
                switch ($request_purpose) {
                    case 'med_exp':
                        return "Medical Expense";
                    case 'hos_bill':
                        return "Hospital Bill";
                    case 'hos_bill_hel':
                        return "Hospital Bill";
                    case 'med_exp_hel':
                        return "Medical Expense";
                    case 'burial':
                        return "Burial";
                    case 'educational':
                        return "Educational";
                    default:
                        return "Unknown";
                }
            }
            $formatted_purpose = str_request_purpose($request_purpose);
            // Get allocation settings
            $settings_stmt = $conn->prepare("SELECT default_amount, validity_months FROM allocation_settings LIMIT 1");
            $settings_stmt->execute();
            $settings = $settings_stmt->fetch(PDO::FETCH_ASSOC);

            // Insert transaction
            $transaction_sql = "INSERT INTO transactions (
                beneficiary_id, 
                patient_name, patient_age, relation, patient_gender, patient_civil_status,
                patient_birthday, patient_birthplace, patient_education, patient_occupation,
                patient_religion, patient_sitio, patient_barangay, 
                patient_complete_address,
                client_name, client_age, client_gender, client_civil_status, client_birthday,
                client_birthplace, client_education, client_occupation, client_religion,
                client_sitio, client_barangay, client_complete_address,
                request_type, request_purpose, request_date, amount, diagnosis_school,
                prep_by, pos_prep, not_by, pos_not, id_type
            ) VALUES (
                :beneficiary_id,
                :patient_name, :patient_age, :relation, :patient_gender, :patient_civil_status,
                :patient_birthday, :patient_birthplace, :patient_education, :patient_occupation,
                :patient_religion, :patient_sitio, :patient_barangay, 
                :patient_complete_address,
                :client_name, :client_age, :client_gender, :client_civil_status, :client_birthday,
                :client_birthplace, :client_education, :client_occupation, :client_religion,
                :client_sitio, :client_barangay, :client_complete_address,
                :request_type, :request_purpose, :request_date, :amount, :diagnosis_school,
                :prep_by, :pos_prep, :not_by, :pos_not, :id_type
            )";

            $transaction_stmt = $conn->prepare($transaction_sql);
            $transaction_stmt->execute([
                ':beneficiary_id' => $beneficiary_id,
                ':patient_name' => $_POST['patient_name'],
                ':patient_age' => $_POST['patient_age'],
                ':relation' => $_POST['relation'],
                ':patient_gender' => $_POST['patient_gender'],
                ':patient_civil_status' => $_POST['patient_civil_status'],
                ':patient_birthday' => $_POST['patient_birthday'],
                ':patient_birthplace' => $_POST['patient_birthplace'],
                ':patient_education' => $_POST['patient_education'],
                ':patient_occupation' => $_POST['patient_occupation'],
                ':patient_religion' => $_POST['patient_religion'],
                ':patient_sitio' => $_POST['patient_sitio'],
                ':patient_barangay' => $_POST['patient_barangay'],
                ':patient_complete_address' => $_POST['patient_complete_address'],
                ':client_name' => $_POST['client_name'],
                ':client_age' => $_POST['client_age'],
                ':client_gender' => $_POST['client_gender'],
                ':client_civil_status' => $_POST['client_civil_status'],
                ':client_birthday' => $_POST['client_birthday'],
                ':client_birthplace' => $_POST['client_birthplace'],
                ':client_education' => $_POST['client_education'],
                ':client_occupation' => $_POST['client_occupation'],
                ':client_religion' => $_POST['client_religion'],
                ':client_sitio' => $_POST['client_sitio'],
                ':client_barangay' => $_POST['client_barangay'],
                ':client_complete_address' => $_POST['client_complete_address'],
                ':request_type' => $_POST['request_type'],
                ':request_purpose' => $formatted_purpose,
                ':request_date' => $_POST['request_date'],
                ':amount' => $_POST['amount'],
                ':diagnosis_school' => $_POST['diagnosis_school'],
                ':prep_by' => $_POST['prep_by'],
                ':pos_prep' => $_POST['pos_prep'],
                ':not_by' => $_POST['not_by'],
                ':pos_not' => $_POST['pos_not'],
                ':id_type' => $_POST['id_type']
            ]);
            $transaction_id = $conn->lastInsertId();

            // Check if beneficiary has money status record
            $check_stmt = $conn->prepare("SELECT id FROM beneficiary_money_status WHERE beneficiary_id = ?");
            $check_stmt->execute([$beneficiary_id]);
            $exists = $check_stmt->fetch();

            if (!$exists) {
                // First transaction - create new record
                $bms_sql = "INSERT INTO beneficiary_money_status 
                            (beneficiary_id, remaining_money, first_transaction_date, last_transaction_date) 
                            VALUES 
                            (:beneficiary_id, :remaining_money, NOW(), NOW())";
                
                $bms_stmt = $conn->prepare($bms_sql);
                $bms_stmt->execute([
                    ':beneficiary_id' => $beneficiary_id,
                    ':remaining_money' => $settings['default_amount'] - $_POST['amount']
                ]);
            } else {
                // Update existing record
                $update_sql = "UPDATE beneficiary_money_status 
                            SET remaining_money = remaining_money - :amount,
                                last_transaction_date = NOW()
                            WHERE beneficiary_id = :beneficiary_id";
                
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->execute([
                    ':amount' => $_POST['amount'],
                    ':beneficiary_id' => $beneficiary_id
                ]);
            }

            $budget_stmt = $conn->prepare("
                SELECT id, amount, remaining_amount 
                FROM budgets 
                WHERE remaining_amount > 0
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $budget_stmt->execute();
            $current_budget = $budget_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$current_budget) {
                throw new Exception("No active budget found. Please add a budget first.");
            }

            // Insert budget transaction record
            $budget_trans_sql = "INSERT INTO budget_transactions 
                (budget_id, transaction_id, amount_used, transaction_date) 
                VALUES 
                (:budget_id, :transaction_id, :amount_used, NOW())";

            $budget_trans_stmt = $conn->prepare($budget_trans_sql);
            $budget_trans_stmt->execute([
                ':budget_id' => $current_budget['id'],
                ':transaction_id' => $transaction_id,
                ':amount_used' => $_POST['amount']
            ]);

            // Update budget remaining amount
            $update_budget_sql = "UPDATE budgets 
                SET remaining_amount = remaining_amount - :amount_used,
                    end_date = CASE 
                        WHEN (remaining_amount - :amount_used) <= 0 THEN CURRENT_DATE
                        ELSE end_date 
                    END
                WHERE id = :budget_id";

            $update_budget_stmt = $conn->prepare($update_budget_sql);
            $update_budget_stmt->execute([
                ':amount_used' => $_POST['amount'],
                ':budget_id' => $current_budget['id']
            ]);

            $reset_requirements_sql = "DELETE FROM beneficiary_requirements WHERE beneficiary_id = :beneficiary_id";

            $reset_stmt = $conn->prepare($reset_requirements_sql);
            $reset_stmt->execute([
                ':beneficiary_id' => $beneficiary_id
            ]);

            // Log the activity
            $log_sql = "INSERT INTO activity_logs (act_name, act_type) VALUES (:act_name, :act_type)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->execute([
                ':act_name' => "Transaction recorded for " . $_POST['patient_name'],
                ':act_type' => "Financial Assistance"
            ]);

            // Commit transaction
            $conn->commit();


        } catch (PDOException $e) {
            $conn->rollBack();
            echo '<div class="message-container error">';
            echo '<h3>Database Error:</h3>';
            echo '<p>' . $e->getMessage() . '</p>';
            echo '<a href="index.php">Try again</a>';
            echo '</div>';
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Document Generation Result</title>
            <link rel="stylesheet" href="css/process.css">
        </head>
        <body>
            <div class="message-container success">
                <h3>Document generated successfully!</h3>
                <p>File saved as: <?php echo $newFileName; ?></p>
                <p>Patient Name: <?php echo htmlspecialchars($_POST['patient_name']); ?></p>
                <p>Request Type: <?php echo htmlspecialchars($_POST['request_type']); ?></p>
                <p>Date Generated: <?php echo date('F d, Y H:i:s'); ?></p>
                <a href="../beneficiaries/view.php?id=<?= $beneficiary_id ?>">Congratulations! GO BACK</a>
            </div>
        </body>
        </html>
        <?php
        
    } catch (Exception $e) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Error</title>
            <style>
                /* Same CSS as above */
            </style>
        </head>
        <body>
            <div class="message-container error">
                <h3>Error:</h3>
                <p><?php echo $e->getMessage(); ?></p>
                <a href="index.php">Try again</a>
            </div>
        </body>
        </html>
        <?php
    }
} else {
    // For non-POST requests, simply redirect to the index
    header("Location: index.php");
    exit;
}


?>