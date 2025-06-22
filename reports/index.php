<?php
include '../includes/auth.php';
include '../includes/db_connection.php';
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

try {
    // Fetching data for age categories from the transactions table
    $stmt = $conn->prepare("SELECT 
        SUM(CASE WHEN patient_age BETWEEN 0 AND 12 THEN 1 ELSE 0 END) AS children,
        SUM(CASE WHEN patient_age BETWEEN 13 AND 24 THEN 1 ELSE 0 END) AS youth,
        SUM(CASE WHEN patient_age BETWEEN 25 AND 59 THEN 1 ELSE 0 END) AS adults,
        SUM(CASE WHEN patient_age >= 60 THEN 1 ELSE 0 END) AS seniors
    FROM transactions");
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Fetch gender count
$male_count = $conn->query("SELECT COUNT(*) FROM beneficiaries WHERE gender = 'Male'")->fetchColumn();
$female_count = $conn->query("SELECT COUNT(*) FROM beneficiaries WHERE gender = 'Female'")->fetchColumn();

$sql = "SELECT request_purpose, COUNT(*) as count 
    FROM transactions 
    WHERE request_purpose IN ('Medical Expense', 'Educational', 'Burial')
    GROUP BY request_purpose";
$stmt = $conn->prepare($sql);
$stmt->execute();

$purpose_labels = [];
$purpose_data = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $purpose_labels[] = $row['request_purpose'];
    $purpose_data[] = $row['count'];
}
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Monitoring Dashboard</h1>
            <div class="date-display" id="displayDate"></div>
        </div>

        <div class="dashboard-grid">
            <div class="chart-card">
                <div class="card-header">
                    <i class="fas fa-chart-pie"></i>
                    <h3>Age Distribution</h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="serviceChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="card-header">
                    <i class="fas fa-users"></i>
                    <h3>Gender Distribution</h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="card-header">
                    <i class="fas fa-chart-line"></i>
                    <h3>Service Types</h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="purposeChart"></canvas>
                </div>
            </div>
        </div>

        <?php include 'money_spe.php'; ?>

        <div class="report-section">
            <form action="generate_report.php" method="post">
                <div class="checkbox-wrapper">
                    <input type="checkbox" id="delete_transactions" name="delete_transactions" value="1">
                    <label for="delete_transactions">Delete transactions after generating report</label>
                </div>
                <button type="submit" class="download-btn">
                    <i class="fas fa-download"></i> Download Quarterly Report
                </button>
            </form>
        </div>
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
        const chartData = {
            age: [
                <?php echo $data['children'] ?>,
                <?php echo $data['youth'] ?>,
                <?php echo $data['adults'] ?>,
                <?php echo $data['seniors'] ?>
            ],
            gender: {
                male: <?php echo $male_count ?>,
                female: <?php echo $female_count ?>
            },
            purpose: {
                labels: <?php echo json_encode($purpose_labels) ?>,
                data: <?php echo json_encode($purpose_data) ?>
            }
        };
    </script>
    <script src="js/dashboard.js"></script>
</body>
</html>
