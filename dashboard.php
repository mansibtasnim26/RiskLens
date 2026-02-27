<?php
// 1. SYSTEM START (Must be at the absolute top)
session_start();
include "db.php"; // This defines $conn

// 2. SECURITY CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// 3. MAP LOGIC (Fetch coordinates for Heatmap)
$map_query = "SELECT a.latitude, a.longitude 
              FROM crime_reports r 
              JOIN areas a ON r.area_id = a.area_id 
              WHERE r.verified = 1";
$map_result = mysqli_query($conn, $map_query);

$locations = [];
if ($map_result) {
    while ($row = mysqli_fetch_assoc($map_result)) {
        if ($row['latitude'] && $row['longitude']) {
            // Push coordinates into array as numbers (floats)
            $locations[] = [(float)$row['latitude'], (float)$row['longitude']];
        }
    }
}
$json_locations = json_encode($locations);

// 4. USER STATISTICS LOGIC
$all_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM crime_reports WHERE user_id = $user_id");
$total_filed = mysqli_fetch_assoc($all_res)['total'];

$ver_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM crime_reports WHERE user_id = $user_id AND verified = 1");
$total_verified = mysqli_fetch_assoc($ver_res)['total'];

$reputation = ($total_verified * 10);

// 5. TOP 5 TRENDS LOGIC (For the table/sidebar)
$stats_query = "SELECT t.type_name, COUNT(r.report_id) as total 
                FROM crime_reports r 
                JOIN crime_types t ON r.type_id = t.type_id 
                WHERE r.verified = 1 
                GROUP BY t.type_name 
                ORDER BY total DESC 
                LIMIT 5";
$stats_result = mysqli_query($conn, $stats_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RiskLens | Dashboard</title>
    <style>
        :root {
            --primary: #0f172a;
            --accent: #3b82f6;
            --danger: #ef4444;
            --success: #16a34a;
            --bg: #f1f5f9;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--primary);
            margin: 0;
            padding: 20px;
            color: #1e293b;
        }

        .dashboard-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .dashboard-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .nav-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            transition: 0.3s;
            display: inline-block;
        }

        .primary-btn {
            background: var(--primary);
            color: white;
        }

        .primary-btn:hover {
            background: var(--accent);
        }

        .accent-btn {
            background: var(--primary);
            color: white;
        }

        .logout-btn {
            background: #fee2e2;
            color: var(--danger);
        }

        .logout-btn:hover {
            background: var(--danger);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-box {
            background: #f8fafc;
            border: 1px solid #7cb2f9ff;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }

        .stat-box h2 {
            margin: 10px 0 0 0;
            font-size: 28px;
            color: var(--accent);
        }

        .stat-box span {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }

        .section-title {
            text-align: center;
            font-size: 22px;
            margin-bottom: 25px;
            color: var(--primary);
            position: relative;
        }

        .report-feed {
            margin-top: 20px;
        }

        .report-item {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            transition: 0.2s;
        }

        .report-item:hover {
            border-color: var(--accent);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .badge {
            background: #fee2e2;
            color: var(--danger);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            text-align: left;
            font-size: 12px;
            color: #64748b;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        td {
            padding: 12px 10px;
            border-bottom: 1px solid #f8fafc;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <div class="dashboard-card">

            <div class="nav-header">
                <div>
                    <h1 style="margin:0; color: #19284bff;">RiskLens Dashboard</h1>
                    <p style="margin:0; color: #64748b;">Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong></p>
                </div>
                <div>
                    <a href="report_crime.php" class="btn primary-btn">+ Report Crime</a>
                    <a href="logout.php" class="btn logout-btn">Logout</a>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-box" style="border: 1px solid #64748b; border-top: 4px solid #0f172a; background: #f8fafc;">
                    <span>Total Reports</span>
                    <h2 style="color: #0f172a;"><?php echo $total_filed; ?></h2>
                </div>

                <div class="stat-box" style="border: 1px solid #16a34a; border-top: 4px solid #16a34a; background: #f0fdf4;">
                    <span>Verified Reports</span>
                    <h2 style="color: #16a34a;"><?php echo $total_verified; ?></h2>
                </div>

                <div class="stat-box" style="border: 1px solid #6366f1; border-top: 4px solid #6366f1; background: #f5f3ff;">
                    <span>Reputation Score</span>
                    <h2 style="color: #4f46e5;"><?php echo $reputation; ?></h2>
                </div>

                <div class="stat-box" style="border: 1px solid #3b82f6; border-top: 4px solid #3b82f6; background: #eff6ff;">
                    <a href="explore_crimes.php" style="text-decoration: none; color: inherit;">
                        <span style="color: #3b82f6; font-weight: bold;">Smart Filter</span>
                        <h2 style="font-size: 20px; color: #1e40af;">🔍 Explore Areas</h2>
                    </a>
                </div>
            </div>
            <hr style="border: 0; height: 2px; background: linear-gradient(to right, transparent, #3b82f6, transparent); margin: 50px 0;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-top: 5px solid #0f172a; border-radius: 20px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">

                <div style="display: flex; align-items: center; margin-bottom: 20px;">
                    <span style="font-size: 24px; margin-right: 12px;">
                        <h3 style="margin: 0; font-size: 20px; color: #19284bff; letter-spacing: -0.5px;">Top Crime Trends</h3>
                        <p style="margin:0; font-size: 15px; color: #475569; font-weight: 500; font-style: italic;">
                            All the crime reports shown here are verified
                        </p>
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 12px; color: #64748b; font-size: 13px; text-transform: uppercase;">Crime Type</th>
                                <th style="padding: 12px; color: #64748b; font-size: 13px; text-transform: uppercase;">Report Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Running the query using your existing variable name
                            $stats_result = mysqli_query($conn, $stats_query);

                            if ($stats_result && mysqli_num_rows($stats_result) > 0) {
                                while ($row = mysqli_fetch_assoc($stats_result)) {
                                    // Using 'total' as per your database column name
                                    $count_value = $row['total'];

                                    echo "<tr style='border-bottom: 1px solid #f1f5f9;'>
                                <td style='padding: 15px; font-weight: 600; color: #1e293b;'>" . htmlspecialchars($row['type_name']) . "</td>
                                <td style='padding: 15px;'>
                                    <span style='background: #fee2e2; color: #ef4444; padding: 4px 12px; border-radius: 20px; font-weight: bold;'>
                                        " . $count_value . "
                                    </span>
                                </td>
                              </tr>";
                                }
                            } else {
                                echo "<tr>
                            <td colspan='2' style='padding: 40px; text-align: center; color: #94a3b8;'>
                                <div style='font-size: 30px;'>📂</div>
                                <p style='margin: 10px 0 0 0;'>No trends found for this area yet.</p>
                            </td>
                          </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr style="border: 0; height: 2px; background: linear-gradient(to right, transparent, #3b82f6, transparent); margin: 50px 0;">
        </div>

        <hr style="border: 0; height: 3px; background: #334155; margin: 40px 0; border-radius: 2px; opacity: 1;">

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-top: 5px solid #3b82f6; border-radius: 20px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-bottom: 40px;">

            <div style="display: flex; align-items: center; margin-bottom: 20px;">
                <span style="font-size: 24px; margin-right: 12px;">
                    <h3 style="margin: 0; font-size: 20px; color: #0f172a; letter-spacing: -0.5px;">Real-time Risk Heatmap</h3>
            </div>

            <div id="map" style="height: 450px; width: 100%; border-radius: 15px; border: 1px solid #e2e8f0;"></div>

            <p style="margin-top: 15px; font-size: 14px; color: #64748b; font-style: italic;">
                * Glowing areas indicate high concentrations of verified reports.
            </p>
        </div>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://leaflet.github.io/Leaflet.heat/dist/leaflet-heat.js"></script>

        <script>
            // Initialize map
            // Initialize map
            var map = L.map('map').setView([23.6850, 90.3563], 7);

            // Add Dark Mode Tiles
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
            }).addTo(map);

            // Get the PHP data
            var heatData = <?php echo $json_locations; ?>;

            // Generate Heatmap with "Glow" settings
            if (heatData.length > 0) {
                var heat = L.heatLayer(heatData, {
                    radius: 35, // Increased for better overlap
                    blur: 25, // Increased for "glow" effect
                    maxZoom: 10,
                    gradient: {
                        0.2: 'blue',
                        0.4: 'cyan',
                        0.6: 'lime',
                        0.8: 'yellow',
                        1.0: 'red'
                    }
                }).addTo(map);
            } else {
                console.log("No heatmap data found.");
            }
        </script>

    </div>
    </div>

</body>

</html>