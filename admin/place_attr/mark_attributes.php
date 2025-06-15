<?php
require_once '../../includes/db.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Check if attributes already exist for this barangay/sitio
        $check_sql = "SELECT id FROM barangay_sitio_attributes 
                     WHERE barangay_id = ? AND (sitio_id = ? OR (sitio_id IS NULL AND ? IS NULL))";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([
            $_POST['barangay_id'],
            !empty($_POST['sitio_id']) ? $_POST['sitio_id'] : null,
            !empty($_POST['sitio_id']) ? $_POST['sitio_id'] : null
        ]);

        if ($check_stmt->fetch()) {
            throw new Exception("Attributes already exist for this location");
        }

        // Insert new attributes
        $sql = "INSERT INTO barangay_sitio_attributes (
            barangay_id, sitio_id, road_access, travel_time_to_market, 
            distance_km, public_transport, communication_signal, 
            near_river, near_ocean, near_forest, hazard_zone, remarks
        ) VALUES (
            :barangay_id, :sitio_id, :road_access, :travel_time_to_market,
            :distance_km, :public_transport, :communication_signal,
            :near_river, :near_ocean, :near_forest, :hazard_zone, :remarks
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':barangay_id' => $_POST['barangay_id'],
            ':sitio_id' => !empty($_POST['sitio_id']) ? $_POST['sitio_id'] : null,
            ':road_access' => $_POST['road_access'],
            ':travel_time_to_market' => $_POST['travel_time_to_market'],
            ':distance_km' => $_POST['distance_km'],
            ':public_transport' => $_POST['public_transport'],
            ':communication_signal' => $_POST['communication_signal'],
            ':near_river' => isset($_POST['near_river']) ? 1 : 0,
            ':near_ocean' => isset($_POST['near_ocean']) ? 1 : 0,
            ':near_forest' => isset($_POST['near_forest']) ? 1 : 0,
            ':hazard_zone' => $_POST['hazard_zone'],
            ':remarks' => $_POST['remarks']
        ]);

        // Log the activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (act_name, act_type) VALUES (?, ?)");
        $log_stmt->execute([
            "Added attributes for barangay ID: " . $_POST['barangay_id'] . 
            (!empty($_POST['sitio_id']) ? " and sitio ID: " . $_POST['sitio_id'] : ""),
            "place_attributes"
        ]);

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Attributes saved successfully'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
}