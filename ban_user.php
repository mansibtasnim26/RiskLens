<?php
session_start();
include "db.php";

if ($_SESSION['role'] === 'admin' && isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Update user status to banned
    $stmt = $conn->prepare("UPDATE users SET status = 'banned' WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        header("Location: admin_logs.php?msg=UserBanned");
    }
}
