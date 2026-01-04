<?php
session_start();
include "db.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Fetch crime types from your 'crime_types' table
$types_query = "SELECT * FROM crime_types";
$types_result = mysqli_query($conn, $types_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>RiskLens | Report Incident</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .report-container {
            background: white;
            width: 600px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            height: 100px;
            margin-bottom: 15px;
        }

        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 15px;
            background: #f8fafc;
        }
    </style>
</head>

<body style="background: #f1f5f9; display: flex; align-items: center; justify-content: center; height: 100vh;">

    <div class="report-container">
        <a href="dashboard.php" style="text-decoration:none; color:#3b82f6; font-size:14px;">← Back to Dashboard</a>
        <h2 style="margin-top:20px; color:#0f172a;">Submit Crime Report</h2>
        <p style="color:#64748b; font-size:14px; margin-bottom:25px;">Provide accurate details to alert the community.</p>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'cat_added'): ?>
            <div style="background: #f0fdf4; color: #166534; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; border: 1px solid #bbf7d0;">
                ✔ New category added successfully! You can now select it below.
            </div>
        <?php endif; ?>

        <form action="submit_report.php" method="POST">
            <label>Type of Crime</label>
            <div style="display: flex; gap: 10px; align-items: center;">
                <select name="type_id" style="margin-bottom: 0;" required>
                    <option value="">-- Select Category --</option>
                    <?php while ($row = mysqli_fetch_assoc($types_result)): ?>
                        <option value="<?php echo $row['type_id']; ?>">
                            <?php echo $row['type_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <a href="add_category.php" style="text-decoration:none; background:#e2e8f0; padding:10px; border-radius:8px; font-size:12px; color:#475569;">+ Add New</a>
            </div>
            <p style="font-size: 11px; color: #64748b; margin-top: 5px; margin-bottom: 15px;">Can't find the category? Click add new.</p>

            <label>City</label>
            <select id="city_select" name="city" required onchange="fetchAreas(this.value)">
                <option value="">-- Select City --</option>
                <?php
                // Get unique cities from your areas table
                $city_res = mysqli_query($conn, "SELECT DISTINCT city FROM areas");
                while ($c = mysqli_fetch_assoc($city_res)) {
                    echo "<option value='" . $c['city'] . "'>" . $c['city'] . "</option>";
                }
                ?>
            </select>

            <label>Area</label>
            <select id="area_select" name="area_id" required>
                <option value="">-- Choose City First --</option>
            </select>

            <script>
                function fetchAreas(cityName) {
                    var areaSelect = document.getElementById("area_select");

                    if (cityName == "") {
                        areaSelect.innerHTML = '<option value="">-- Choose City First --</option>';
                        return;
                    }

                    // Use AJAX to call get_areas.php
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "get_areas.php", true);
                    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            areaSelect.innerHTML = this.responseText;
                        }
                    };
                    xhr.send("city=" + cityName);
                }
            </script>

            <label>Description</label>
            <textarea name="description" placeholder="Describe what happened..." required></textarea>

            <button type="submit" class="primary-btn">Submit Report</button>
        </form>
    </div>

</body>