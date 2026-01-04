<?php
session_start();
include "db.php";

// Check if Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

$query = "SELECT s.log_id, u.name, s.reason, s.timestamp, u.user_id 
          FROM suspicious_log_activity s 
          JOIN users u ON s.user_id = u.user_id 
          ORDER BY s.timestamp DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>RiskLens Admin | Logs</title>
    <link rel="stylesheet" href="style.css">
</head>

<body style="background: #f1f5f9; padding: 40px;">

    <div style="max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #ef4444;">⚠️ Suspicious Activity Monitor</h2>
        <hr>

        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background: #f8fafc; text-align: left; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px;">User</th>
                    <th style="padding: 12px;">Reason / Violation</th>
                    <th style="padding: 12px;">Date & Time</th>
                    <th style="padding: 12px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px;"><strong><?php echo $row['name']; ?></strong></td>
                        <td style="padding: 12px; color: #ef4444;"><?php echo $row['reason']; ?></td>
                        <td style="padding: 12px; font-size: 13px; color: #64748b;"><?php echo $row['timestamp']; ?></td>
                        <td style="padding: 12px;">
                            <a href="ban_user.php?id=<?php echo $row['user_id']; ?>"
                                style="background: #000; color: #fff; padding: 5px 10px; text-decoration: none; border-radius: 5px; font-size: 12px;">
                                Ban User
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>