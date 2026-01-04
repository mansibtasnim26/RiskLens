<?php
include "db.php";

$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$area_id = $_POST['area_id'];
// Using password_hash is correct - this is the secure way!
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Check if email or phone exists
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
$stmt->bind_param("ss", $email, $phone);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    header("Location: signup.html?status=exists");
} else {
    // Note: We don't need to insert 'role' here because the DB defaults it to 'user'
    $ins = $conn->prepare("INSERT INTO users (name, email, phone, password, area_id, date_joined) VALUES (?, ?, ?, ?, ?, NOW())");
    $ins->bind_param("ssssi", $name, $email, $phone, $password, $area_id);

    if ($ins->execute()) {
        header("Location: login.html?status=success");
    } else {
        // Helpful for debugging: uncomment the line below if it keeps failing
        // echo "Error: " . $ins->error; 
        header("Location: signup.html?status=fail");
    }
}
exit();
