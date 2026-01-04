<?php
include "db.php";

if (isset($_POST['city'])) {
    $city = $_POST['city'];
    $stmt = $conn->prepare("SELECT area_id, area FROM areas WHERE city = ?");
    $stmt->bind_param("s", $city);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<option value="">-- Select Area --</option>';
        while ($row = $result->fetch_assoc()) {
            // The user sees $row['area'], but the form sends $row['area_id']
            echo '<option value="' . $row['area_id'] . '">' . htmlspecialchars($row['area']) . '</option>';
        }
    } else {
        echo '<option value="">No areas found</option>';
    }
}
