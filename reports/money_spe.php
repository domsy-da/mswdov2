<?php

// Get the current date
$current_date = date('Y-m-d');
$year = date('Y');

// Determine the current quarter's start and end dates
if ($current_date >= "$year-01-01" && $current_date <= "$year-03-31") {
    $start_date = "$year-01-01";
    $end_date = "$year-03-31";
    $quarter = "First Quarter";
} elseif ($current_date >= "$year-04-01" && $current_date <= "$year-06-30") {
    $start_date = "$year-04-01";
    $end_date = "$year-06-30";
    $quarter = "Second Quarter";
} elseif ($current_date >= "$year-07-01" && $current_date <= "$year-09-30") {
    $start_date = "$year-07-01";
    $end_date = "$year-09-30";
    $quarter = "Third Quarter";
} else {
    $start_date = "$year-10-01";
    $end_date = "$year-12-31";
    $quarter = "Fourth Quarter";
}

// Convert dates to a more readable format
$start_date_formatted = date("F j, Y", strtotime($start_date));
$end_date_formatted = date("F j, Y", strtotime($end_date));

// Get the total amount spent for the current quarter
$query = "SELECT SUM(amount) AS total_spent FROM transactions WHERE request_date BETWEEN ? AND ?";
$stmt = $conn->prepare($query);
$stmt->execute([$start_date, $end_date]);
$total_spent = $stmt->fetch(PDO::FETCH_ASSOC)['total_spent'] ?? 0;
?>

<link rel="stylesheet" href="css/ms.css">

<div class="containerQ">
    <h2>Quarterly Spending Report</h2>
    
    <!-- Display Current Quarter -->
    <div id="displayQuarter">
        <?php echo "<b>$quarter</b> ($start_date_formatted to $end_date_formatted)"; ?>
    </div>

    <!-- Total Money Spent Display -->
    <div class="total-spent">
        <h3>Total Money Spent:</h3>
        <p id="totalAmount">₱<?= number_format($total_spent, 2) ?></p>
    </div>

    <!-- View Details Button -->
    <a href="detailed_expenses.php" class="btn btn-ys">View Detailed Expenses</a>
</div>

</body>
</html>
