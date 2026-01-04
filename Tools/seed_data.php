<?php
include "db.php";

// 1. Fetch all 202 User IDs dynamically
$user_ids = [];
$res_u = mysqli_query($conn, "SELECT user_id FROM users");
while ($u = mysqli_fetch_assoc($res_u)) {
    $user_ids[] = $u['user_id'];
}

// 2. Fetch all 100 Area IDs dynamically
$area_ids = [];
$res_a = mysqli_query($conn, "SELECT area_id FROM areas");
while ($a = mysqli_fetch_assoc($res_a)) {
    $area_ids[] = $a['area_id'];
}

// 3. Fetch all Crime Type IDs (Theft, Robbery, etc.)
$type_ids = [];
$res_t = mysqli_query($conn, "SELECT type_id FROM crime_types");
while ($t = mysqli_fetch_assoc($res_t)) {
    $type_ids[] = $t['type_id'];
}

// Safety check: Don't run if tables are empty
if (empty($user_ids) || empty($area_ids) || empty($type_ids)) {
    die("Error: Please ensure you have data in users, areas, and crime_types tables first!");
}

echo "Starting to plant 500 reports across " . count($user_ids) . " users...<br>";

for ($i = 1; $i <= 500; $i++) {
    // Pick a random ID from the arrays we built
    $random_user = $user_ids[array_rand($user_ids)];
    $random_area = $area_ids[array_rand($area_ids)];
    $random_type = $type_ids[array_rand($type_ids)];

    // Random descriptions
    $descriptions = [
        "Unauthorized activity reported in the evening.",
        "Theft incident involving personal belongings.",
        "Suspicious person seen loitering near the entrance.",
        "Vandalism discovered on public property.",
        "Snatching incident reported by a local resident."
    ];
    $desc = $descriptions[array_rand($descriptions)] . " (Reference ID: #$i)";

    // Randomize time over the last 6 months to make the heatmap look historical
    $random_timestamp = mt_rand(strtotime("-6 months"), time());
    $created_at = date("Y-m-d H:i:s", $random_timestamp);

    // 70% chance of being verified (to test your verification UI)
    $verified = (rand(1, 10) > 3) ? 1 : 0;

    $sql = "INSERT INTO crime_reports (user_id, type_id, area_id, description, verified, created_at) 
            VALUES ($random_user, $random_type, $random_area, '$desc', $verified, '$created_at')";

    mysqli_query($conn, $sql);
}

echo "Success! 500 reports have been linked to your 202 users.";
