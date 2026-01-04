<?php
include "db.php";

// 1. Get all available Area IDs from your areas table
$area_ids = [];
$res = mysqli_query($conn, "SELECT area_id FROM areas");
while ($row = mysqli_fetch_assoc($res)) {
    $area_ids[] = $row['area_id'];
}

if (empty($area_ids)) {
    die("Error: Please populate the 'areas' table first so users can be assigned to locations.");
}

$first_names = ['Arif', 'Sajid', 'Mehedi', 'Fariha', 'Zubair', 'Kamrul', 'Mitu', 'Rakib', 'Sumaiya', 'Nayem', 'Farhana', 'Istiak', 'Rina', 'Tariqul', 'Jannat', 'Mahbub', 'Abir', 'Sultana', 'Tanvir', 'Nusrat'];
$last_names = ['Rahman', 'Ahmed', 'Hossain', 'Jahan', 'Islam', 'Hasan', 'Khan', 'Akter', 'Uddin', 'Yasmin', 'Begum', 'Ferdous', 'Chowdhury', 'Sarker'];

echo "Starting to create 100 users...<br>";

for ($i = 1; $i <= 100; $i++) {
    $name = $first_names[array_rand($first_names)] . ' ' . $last_names[array_rand($last_names)];
    $email = strtolower(str_replace(' ', '.', $name)) . $i . "@example.com";
    $phone = "01" . rand(3, 9) . rand(10000000, 99999999); // Generates valid-looking BD mobile number
    $password = password_hash("123", PASSWORD_DEFAULT); // Secure hashing
    $area_id = $area_ids[array_rand($area_ids)]; // Randomly assign to one of your 100 areas
    $role = 'user';

    // Randomize join date over the last year
    $random_timestamp = mt_rand(strtotime("-1 year"), time());
    $date_joined = date("Y-m-d H:i:s", $random_timestamp);

    $sql = "INSERT INTO users (name, email, phone, password, area_id, date_joined, role) 
            VALUES ('$name', '$email', '$phone', '$password', $area_id, '$date_joined', '$role')";

    mysqli_query($conn, $sql);
}

echo "Success! 100 users added to the database.";
