<?php
$conn = mysqli_connect("localhost", "root", "", "risklens");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
