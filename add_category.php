<?php
session_start();
include "db.php";

// If user is not logged in, kick them out
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_category = $_POST['category_name'];

    // Insert the new type into your crime_types table
    $stmt = $conn->prepare("INSERT INTO crime_types (type_name) VALUES (?)");
    $stmt->bind_param("s", $new_category);

    if ($stmt->execute()) {
        // SUCCESS: Send them back to the reporting page
        header("Location: report_crime.php?msg=cat_added");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>RiskLens | New Category</title>
    <link rel="stylesheet" href="style.css">
</head>

<body style="background: #f1f5f9; display: flex; align-items: center; justify-content: center; height: 100vh;">
    <div style="background: white; padding: 40px; border-radius: 24px; width: 400px; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
        <h2 style="color:#0f172a; margin-bottom: 10px;">Add New Category</h2>
        <p style="color:#64748b; font-size:14px; margin-bottom:20px;">This will add a new option to the crime selection menu.</p>

        <form action="add_category.php" method="POST">
            <div class="input-group">
                <label>Category Name</label>
                <input type="text" name="category_name" placeholder="e.g., Shoplifting" required autofocus>
            </div>
            <button type="submit" class="primary-btn">Add & Return</button>
            <a href="report_crime.php" style="display:block; text-align:center; margin-top:15px; font-size:13px; color:#94a3b8; text-decoration:none;">Cancel</a>
        </form>
    </div>
</body>

</html>