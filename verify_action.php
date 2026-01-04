<?php
session_start();
include "db.php";

// 1. Security check: Only admins allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $report_id = $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'verify') {
        // If verify is clicked, set verified to 1
        $stmt = $conn->prepare("UPDATE crime_reports SET verified = 1 WHERE report_id = ?");
    } else if ($action === 'reject') {
        // If reject is clicked, remove the report from the database
        $stmt = $conn->prepare("DELETE FROM crime_reports WHERE report_id = ?");
    }

    $stmt->bind_param("i", $report_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?msg=" . $action . "_success");
    } else {
        echo "Error: " . $conn->error;
    }
    exit();
}
