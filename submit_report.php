<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Get data from the form and session
    $user_id = $_SESSION['user_id'];
    $type_id = $_POST['type_id'];
    $area_id = $_POST['area_id'];
    $description = $_POST['description'];

    // 2. Prepare the SQL to match YOUR table columns
    // We don't need to insert 'verified' or 'votes' because they have DEFAULT values in your SQL
    $sql = "INSERT INTO crime_reports (user_id, area_id, type_id, description, date_reported, time_reported) 
            VALUES (?, ?, ?, ?, CURDATE(), CURTIME())";

    $stmt = $conn->prepare($sql);

    // 3. Bind the 4 inputs (iii for integers, s for string)
    $stmt->bind_param("iiis", $user_id, $area_id, $type_id, $description);

    if ($stmt->execute()) {
        header("Location: dashboard.php?report=success");
    } else {
        echo "Database Error: " . $stmt->error;
    }
    exit();
}
