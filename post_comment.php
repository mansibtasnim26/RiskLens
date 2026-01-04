<?php
session_start();
include "db.php";

if (isset($_POST['report_id']) && isset($_POST['comment_text'])) {
    $user_id = $_SESSION['user_id'];
    $report_id = $_POST['report_id'];
    $comment_body = trim($_POST['comment_text']);

    if (!empty($comment_body)) {
        // Using 'text' as per your CREATE TABLE schema
        $stmt = $conn->prepare("INSERT INTO comments (report_id, user_id, text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $report_id, $user_id, $comment_body);

        if ($stmt->execute()) {
            echo "success";
        }
    }
}
