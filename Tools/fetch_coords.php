<?php
include "db.php";
set_time_limit(0);
// 1. Corrected the column name from 'area_name' to 'area'
$result = mysqli_query($conn, "SELECT area_id, area FROM areas WHERE latitude IS NULL");

while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['area_id'];
    $address = urlencode($row['area']); // Using 'area' here too

    // Using Nominatim (OpenStreetMap) API
    $url = "https://nominatim.openstreetmap.org/search?format=json&q=$address";

    // Nominatim requires a User-Agent header
    $opts = ['http' => ['method' => "GET", 'header' => "User-Agent: RiskLensProject/1.0\r\n"]];
    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);

    if (!empty($data)) {
        $lat = $data[0]['lat'];
        $lon = $data[0]['lon'];

        $update_sql = "UPDATE areas SET latitude = '$lat', longitude = '$lon' WHERE area_id = $id";
        mysqli_query($conn, $update_sql);
        echo "Updated: " . $row['area'] . " ($lat, $lon)<br>";
    } else {
        echo "Could not find coordinates for: " . $row['area'] . "<br>";
    }

    // Wait 1 second to respect API usage limits
    sleep(1);
}

echo "Finished updating coordinates!";
