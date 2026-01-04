<?php
session_start();
include "db.php";

// 1. Security: If not admin, kick them out
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

// 2. Fetch Admin Stats
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM crime_reports WHERE verified = 0"))['count'];

// 3. Count Suspicious activities happened today
$log_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM suspicious_log_activity WHERE DATE(timestamp) = CURDATE()");
$suspicious_today = mysqli_fetch_assoc($log_count_res)['total'];

// 4. Fetch Pending Reports for the table
$query = "SELECT r.report_id, r.description, r.date_reported, t.type_name, u.name as reporter 
          FROM crime_reports r 
          JOIN crime_types t ON r.type_id = t.type_id 
          JOIN users u ON r.user_id = u.user_id 
          WHERE r.verified = 0 
          ORDER BY r.date_reported DESC";
$pending_result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>RiskLens | Admin Dashboard</title>
    <style>
        body {
            background: #0f172a;
            font-family: 'Inter', sans-serif;
            padding: 40px;
            margin: 0;
        }

        .admin-container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #f8fafc;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .stat-card h2 {
            font-size: 36px;
            margin: 10px 0;
        }

        .stat-link {
            font-size: 12px;
            text-decoration: none;
            font-weight: 600;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            text-align: left;
            background: #f1f5f9;
            padding: 15px;
            color: #475569;
            font-size: 13px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: #1e293b;
        }

        .verify-btn {
            background: #16a34a;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 12px;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="admin-container">
        <div class="header-flex">
            <div>
                <h1 style="margin:0; font-size: 26px; color: #0f172a;">RiskLens Command Center</h1>
                <p style="color: #64748b; margin: 5px 0 0 0;">System Overview & Integrity Monitoring</p>
            </div>
            <a href="logout.php" style="background: #fee2e2; color: #ef4444; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px;">Logout</a>
        </div>

        <div class="stat-grid">
            <div class="stat-card" style="border: 1px solid #3b82f6; border-top: 4px solid #3b82f6; background: #eff6ff;">
                <p style="color: #64748b; margin: 0; font-size: 14px;">Verified Members</p>
                <h2 style="color: #1e40af;"><?php echo $total_users; ?></h2>
                <span style="color: #60a5fa; font-size: 11px;">Active Community</span>
            </div>

            <div class="stat-card" style="border: 1px solid #f59e0b; border-top: 4px solid #f59e0b; background: #fffbeb;">
                <p style="color: #64748b; margin: 0; font-size: 14px;">Reports Awaiting Action</p>
                <h2 style="color: #b45309;"><?php echo $pending_count; ?></h2>
                <a href="#pending-table" class="stat-link" style="color: #d97706;">Scroll to Review ↓</a>
            </div>

            <div class="stat-card" style="border: 1px solid #ef4444; border-top: 4px solid #ef4444; background: #fef2f2;">
                <p style="color: #64748b; margin: 0; font-size: 14px;">Suspicious Flags (Today)</p>
                <h2 style="color: #b91c1c;"><?php echo $suspicious_today; ?></h2>
                <a href="admin_logs.php" class="stat-link" style="color: #ef4444;">Access Audit Logs →</a>
            </div>
        </div>

        <div id="pending-table" style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-top: 20px;">
            <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; background: #fafafa;">
                <h3 style="margin: 0; font-size: 18px; color: #1e293b;">Queue: Pending Verification</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Reporter</th>
                        <th>Category</th>
                        <th>Details</th>
                        <th>Timestamp</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($pending_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($pending_result)): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['reporter']); ?></strong></td>
                                <td><span style="background: #eff6ff; color: #3b82f6; padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 11px;"><?php echo strtoupper($row['type_name']); ?></span></td>
                                <td style="max-width: 350px; line-height: 1.4;"><?php echo htmlspecialchars($row['description']); ?></td>
                                <td style="color: #64748b; white-space: nowrap;"><?php echo date('M d, H:i', strtotime($row['date_reported'])); ?></td>
                                <td style="white-space: nowrap;">
                                    <a href="verify_action.php?id=<?php echo $row['report_id']; ?>&action=verify" class="verify-btn">Approve</a>
                                    <a href="verify_action.php?id=<?php echo $row['report_id']; ?>&action=reject"
                                        onclick="return confirm('Permanently delete this report?')"
                                        style="color: #94a3b8; font-size: 12px; margin-left: 12px; text-decoration: none;">Discard</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 60px; color: #94a3b8;">
                                <div style="font-size: 40px; margin-bottom: 10px;">✨</div>
                                <strong>Clear skies!</strong><br>All community reports have been processed.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>