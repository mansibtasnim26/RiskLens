<?php
include "db.php";
$area_id = $_POST['area_id'];

$query = "SELECT t.type_id, t.type_name, COUNT(r.report_id) as total 
          FROM crime_reports r 
          JOIN crime_types t ON r.type_id = t.type_id 
          WHERE r.area_id = ? AND r.verified = 1 
          GROUP BY t.type_id ORDER BY total DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $area_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<div class='card' style='animation: fadeIn 0.5s;'>";
echo "<h3 style='margin-top:0; font-size:18px;'>Common Crimes in this Area</h3>";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='stat-row' onclick='showDetails($area_id, " . $row['type_id'] . ")'>
                <span style='font-weight:600; color:#1e293b;'>" . $row['type_name'] . "</span>
                <span class='badge-count'>" . $row['total'] . " Reports</span>
              </div>";
    }
    echo "<p style='font-size:12px; color:#64748b; text-align:center;'>Click a category to see descriptions</p>";
} else {
    echo "<p style='color:#94a3b8; text-align:center;'>No verified data for this location.</p>";
}
echo "</div>";
