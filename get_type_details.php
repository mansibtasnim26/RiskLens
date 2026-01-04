<?php
include "db.php";
$area_id = $_POST['area_id'];
$type_id = $_POST['type_id'];

// Get reports with vote counts
$query = "SELECT r.report_id, r.description, r.date_reported, 
          (SELECT COUNT(*) FROM votes v WHERE v.report_id = r.report_id AND v.vote_type = 'up') as upvote_count
          FROM crime_reports r 
          WHERE r.area_id = ? AND r.type_id = ? AND r.verified = 1 
          ORDER BY r.date_reported DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $area_id, $type_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<div class='card' style='border-top: 4px solid #3b82f6;'>";
echo "<h3 style='margin-top:0;'>Incident Details</h3>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $r_id = $row['report_id'];
        echo "
        <div class='report-detail-item' style='margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid #f1f5f9;'>
            <p style='margin:0 0 10px 0; color:#334155; font-size:16px;'>" . htmlspecialchars($row['description']) . "</p>
            <div style='display:flex; justify-content:space-between; align-items:center;'>
                <small style='color:#94a3b8;'>📅 " . date('M d, Y', strtotime($row['date_reported'])) . "</small>
                
                <div>
                    <button onclick='handleVote($r_id)' class='btn' style='background:#f1f5f9; color:#3b82f6; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; font-size:12px;'>
                        ▲ Upvote <span id='vote-count-$r_id'>" . $row['upvote_count'] . "</span>
                    </button>
                    <button onclick='toggleComments($r_id)' class='btn' style='background:#f1f5f9; color:#64748b; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; font-size:12px; margin-left:5px;'>
                        💬 Comments
                    </button>
                </div>
            </div>

            <div id='comment-section-$r_id' style='display:none; margin-top:15px; background:#f8fafc; padding:10px; border-radius:8px;'>
                <div id='comment-list-$r_id' style='max-height:200px; overflow-y:auto;'></div>
                <div style='display:flex; margin-top:10px;'>
                    <input type='text' id='input-$r_id' placeholder='Add a comment...' style='flex:1; padding:5px; border:1px solid #ddd; border-radius:4px;'>
                    <button onclick='submitComment($r_id)' style='background:#3b82f6; color:white; border:none; padding:5px 10px; border-radius:4px; margin-left:5px;'>Post</button>
                </div>
            </div>
        </div>";
    }
} else {
    echo "<p>No reports found for this category.</p>";
}
echo "</div>";
