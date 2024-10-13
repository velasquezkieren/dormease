<?php
include('../config.php');
session_start();

if (!isset($_SESSION["a_ID"])) {
    header("location:login");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle editing of dormitory
    if (isset($_POST['edit_dormitory'])) {
        $d_ID = mysqli_real_escape_string($con, $_POST['d_ID']);
        $d_Name = mysqli_real_escape_string($con, $_POST['d_Name']);
        $d_Street = mysqli_real_escape_string($con, $_POST['d_Street']);
        $d_City = mysqli_real_escape_string($con, $_POST['d_City']);
        $d_Description = mysqli_real_escape_string($con, $_POST['d_Description']);

        // Update query
        $updateQuery = "UPDATE dormitory SET d_Name = '$d_Name', d_Street = '$d_Street', d_City = '$d_City', d_Description = '$d_Description' WHERE d_ID = '$d_ID'";
        if ($con->query($updateQuery) === TRUE) {
            header("Location: index.php?status=success");
        } else {
            header("Location: index.php?status=error");
        }
        exit();
    }

    // Handle deletion of dormitory
    if (isset($_POST['delete_dormitory'])) {
        $d_ID = mysqli_real_escape_string($con, $_POST['d_ID']);

        // Delete query
        $deleteQuery = "DELETE FROM dormitory WHERE d_ID = '$d_ID'";
        if ($con->query($deleteQuery) === TRUE) {
            header("Location: index.php?status=success");
        } else {
            header("Location: index.php?status=error");
        }
        exit();
    }
}
