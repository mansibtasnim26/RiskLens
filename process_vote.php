<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    die("Login required");
}

$user_id = $_SESSION['user_id'];
$report_id = $_POST['report_id'];

// We use 'up' as the default vote_type per your ENUM
$query = "INSERT INTO votes (report_id, user_id, vote_type) VALUES (?, ?, 'up')";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $report_id, $user_id);

try {
    if ($stmt->execute()) {
        echo "success";
    }
} catch (mysqli_sql_exception $e) {
    // This catches the UNIQUE KEY constraint if they already voted
    echo "Already voted";
}
