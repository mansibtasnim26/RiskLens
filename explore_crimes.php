<?php
session_start();
include "db.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RiskLens | Explore Areas</title>
    <style>
        :root {
            --primary: #0f172a;
            --accent: #3b82f6;
            --bg: #f1f5f9;
            --danger: #ef4444;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--primary);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
        }

        select {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 15px;
            outline: none;
        }

        select:focus {
            border-color: var(--accent);
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: 0.2s;
        }

        .stat-row:hover {
            border-color: var(--accent);
            transform: translateX(5px);
        }

        .btn-back {
            text-decoration: none;
            color: var(--accent);
            font-weight: bold;
            font-size: 14px;
        }

        .badge-count {
            background: var(--accent);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .report-detail-item {
            border-left: 4px solid var(--danger);
            padding: 15px;
            background: #fff;
            margin-top: 10px;
            border-radius: 0 8px 8px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="card">
            <div class="header-flex">
                <h1 style="margin:0; font-size: 24px;">Explore Local Risks</h1>
                <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
            </div>

            <p style="color: #64748b; margin-bottom: 20px;">Select a city and area to see the most frequent crimes reported by the community.</p>

            <div class="filter-section">
                <select id="city_select" onchange="fetchAreas(this.value)">
                    <option value="">-- Select City --</option>
                    <?php
                    $cities = mysqli_query($conn, "SELECT DISTINCT city FROM areas");
                    while ($c = mysqli_fetch_assoc($cities)) echo "<option value='" . $c['city'] . "'>" . $c['city'] . "</option>";
                    ?>
                </select>

                <select id="area_select" onchange="fetchStats(this.value)">
                    <option value="">-- Select Area --</option>
                </select>
            </div>
        </div>

        <div id="stats_container"></div>

        <div id="reports_detail"></div>
    </div>

    <script>
        function fetchAreas(city) {
            if (!city) return;
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "get_areas.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                document.getElementById("area_select").innerHTML = this.responseText;
                document.getElementById("stats_container").innerHTML = "";
                document.getElementById("reports_detail").innerHTML = "";
            };
            xhr.send("city=" + city);
        }

        function fetchStats(areaId) {
            if (!areaId) return;
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "get_area_stats.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                document.getElementById("stats_container").innerHTML = this.responseText;
                document.getElementById("reports_detail").innerHTML = "";
            };
            xhr.send("area_id=" + areaId);
        }

        function showDetails(areaId, typeId) {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "get_type_details.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                document.getElementById("reports_detail").innerHTML = this.responseText;
                window.scrollTo({
                    top: document.getElementById("reports_detail").offsetTop - 20,
                    behavior: 'smooth'
                });
            };
            xhr.send("area_id=" + areaId + "&type_id=" + typeId);
        }

        // --- 2. Community Interaction Logic (Votes & Comments) ---

        function handleVote(reportId) {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "process_vote.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (this.responseText.trim() == "success") {
                    let countSpan = document.getElementById("vote-count-" + reportId);
                    countSpan.innerText = parseInt(countSpan.innerText) + 1;
                } else {
                    alert(this.responseText); // Shows "Already voted"
                }
            };
            xhr.send("report_id=" + reportId);
        }

        function toggleComments(reportId) {
            let section = document.getElementById("comment-section-" + reportId);
            if (section.style.display === "none") {
                section.style.display = "block";
                loadComments(reportId);
            } else {
                section.style.display = "none";
            }
        }

        function loadComments(reportId) {
            let xhr = new XMLHttpRequest();
            xhr.open("GET", "get_comments.php?report_id=" + reportId, true);
            xhr.onload = function() {
                document.getElementById("comment-list-" + reportId).innerHTML = this.responseText;
            };
            xhr.send();
        }

        function submitComment(reportId) {
            let textInput = document.getElementById("input-" + reportId);
            let text = textInput.value;
            if (!text) return;

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "post_comment.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (this.responseText.trim() == "success") {
                    textInput.value = "";
                    loadComments(reportId); // Refresh the list to show the new comment
                }
            };
            xhr.send("report_id=" + reportId + "&comment_text=" + encodeURIComponent(text));
        }
    </script>

</body>

</html>