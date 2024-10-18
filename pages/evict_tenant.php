<?php
// Evict tenant from a specific occupancy
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

// Get the tenant ID and room ID from the URL
if (isset($_GET['id']) && isset($_GET['room_id'])) {
    $tenantID = $_GET['id'];
    $roomID = $_GET['room_id'];

    // Delete the specific occupancy record
    $query = "DELETE FROM occupancy WHERE o_Occupant = '$tenantID' AND o_Room = '$roomID'";

    if (mysqli_query($con, $query)) {
        // Increment the room capacity by 1
        $updateQuery = "UPDATE room SET r_Capacity = r_Capacity + 1, r_Availability = 1 WHERE r_ID = '$roomID'";
        if (mysqli_query($con, $updateQuery)) {
            header("Location: tenants?status=evicted");
            exit();
        } else {
            die('Update failed: ' . mysqli_error($con));
        }
    } else {
        die('Query failed: ' . mysqli_error($con));
    }
} else {
    die('Invalid request.');
}

// Close database connection
mysqli_close($con);
