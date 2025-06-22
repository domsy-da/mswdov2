<?php
include 'config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['beneficiary_id'], $data['transaction_id'], $data['request_amount'], $data['patient_name'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Missing required data.'
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    $beneficiary_id = $data['beneficiary_id'];
    $transaction_id = $data['transaction_id'];
    $amount = $data['request_amount'];

    // Settings
    $settings_stmt = $pdo->prepare("SELECT default_amount, validity_months FROM allocation_settings LIMIT 1");
    $settings_stmt->execute();
    $settings = $settings_stmt->fetch(PDO::FETCH_ASSOC);

    // Check money status
    $check_stmt = $pdo->prepare("SELECT id FROM beneficiary_money_status WHERE beneficiary_id = ?");
    $check_stmt->execute([$beneficiary_id]);
    $exists = $check_stmt->fetch();

    if (!$exists) {
        $bms_sql = "INSERT INTO beneficiary_money_status
                    (beneficiary_id, remaining_money, first_transaction_date, last_transaction_date)
                    VALUES
                    (:beneficiary_id, :remaining_money, NOW(), NOW())";
        $bms_stmt = $pdo->prepare($bms_sql);
        $bms_stmt->execute([
            ':beneficiary_id' => $beneficiary_id,
            ':remaining_money' => $settings['default_amount'] - $amount
        ]);
    } else {
        $update_sql = "UPDATE beneficiary_money_status
                    SET remaining_money = remaining_money - :amount,
                        last_transaction_date = NOW()
                    WHERE beneficiary_id = :beneficiary_id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([
            ':amount' => $amount,
            ':beneficiary_id' => $beneficiary_id
        ]);
    }

    // Budget
    $budget_stmt = $pdo->prepare("
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

    $budget_trans_sql = "INSERT INTO budget_transactions
        (budget_id, transaction_id, amount_used, transaction_date)
        VALUES
        (:budget_id, :transaction_id, :amount_used, NOW())";
    $budget_trans_stmt = $pdo->prepare($budget_trans_sql);
    $budget_trans_stmt->execute([
        ':budget_id' => $current_budget['id'],
        ':transaction_id' => $transaction_id,
        ':amount_used' => $amount
    ]);

    $update_budget_sql = "UPDATE budgets
        SET remaining_amount = remaining_amount - :amount_used_1,
            end_date = CASE
                WHEN (remaining_amount - :amount_used_2) <= 0 THEN CURRENT_DATE
                ELSE end_date
            END
        WHERE id = :budget_id";
    $update_budget_stmt = $pdo->prepare($update_budget_sql);
    $update_budget_stmt->execute([
        ':amount_used_1' => $amount, // First instance
        ':amount_used_2' => $amount, // Second instance
        ':budget_id' => $current_budget['id']
    ]);

    // Reset requirements
    $reset_requirements_sql = "DELETE FROM beneficiary_requirements WHERE beneficiary_id = :beneficiary_id";
    $reset_stmt = $pdo->prepare($reset_requirements_sql);
    $reset_stmt->execute([
        ':beneficiary_id' => $beneficiary_id
    ]);

    // Log
    $log_sql = "INSERT INTO activity_logs (act_name, act_type) VALUES (:act_name, :act_type)";
    $log_stmt = $pdo->prepare($log_sql);
    $log_stmt->execute([
        ':act_name' => "Transaction recorded for " . $data['patient_name'],
        ':act_type' => "Financial Assistance"
    ]);

    $pdo->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Finalize transaction failed: " . $e->getMessage()); // Log the error for debugging
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>