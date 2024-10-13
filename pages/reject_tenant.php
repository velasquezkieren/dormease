<?php

// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

// Get the tenant ID from the URL
if (isset($_GET['id'])) {
    $tenantID = $_GET['id'];

    // Update the o_Status to 2 (rejected)
    $query = "UPDATE occupancy SET o_Status = 2 WHERE o_Occupant = '$tenantID'";
    if (mysqli_query($con, $query)) {
        header("Location: tenants.php?status=rejected");
    } else {
        die('Query failed: ' . mysqli_error($con));
    }
} else {
    die('Invalid request.');
}

// Close database connection
mysqli_close($con);
