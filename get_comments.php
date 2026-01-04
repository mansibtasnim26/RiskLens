<?php
include "db.php";
$report_id = $_GET['report_id'];

// Joining with users table to get the commenter's name
$query = "SELECT c.text, c.timestamp, u.name 
          FROM comments c 
          JOIN users u ON c.user_id = u.user_id 
          WHERE c.report_id = ? 
          ORDER BY c.timestamp ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div style='margin-bottom: 8px; padding: 8px; background: #fff; border-radius: 5px; border: 1px solid #edf2f7;'>
                <span style='font-weight: bold; font-size: 13px; color: #3b82f6;'>" . htmlspecialchars($row['name']) . "</span>
                <p style='margin: 4px 0; font-size: 14px; color: #334155;'>" . htmlspecialchars($row['text']) . "</p>
                <small style='font-size: 10px; color: #94a3b8;'>" . date('M d, H:i', strtotime($row['timestamp'])) . "</small>
              </div>";
    }
} else {
    echo "<p style='font-size: 13px; color: #94a3b8; text-align: center;'>No comments yet.</p>";
}
