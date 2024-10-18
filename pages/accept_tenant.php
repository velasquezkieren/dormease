<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

// Get the tenant ID from the URL
if (isset($_GET['id'])) {
    $tenantID = $_GET['id'];

    // Update the o_Status to 1 (accepted)
    $query = "UPDATE occupancy SET o_Status = 1 WHERE o_Occupant = '$tenantID'";

    if (mysqli_query($con, $query)) {
        // Get the room ID associated with the tenant
        $roomQuery = "SELECT o_Room FROM occupancy WHERE o_Occupant = '$tenantID'";
        $roomResult = mysqli_query($con, $roomQuery);

        if ($roomResult && mysqli_num_rows($roomResult) > 0) {
            $roomRow = mysqli_fetch_assoc($roomResult);
            $roomID = $roomRow['o_Room'];

            // Update the room's capacity and availability
            $updateRoomQuery = "UPDATE room 
                                SET r_Capacity = CASE 
                                    WHEN r_Capacity > 0 THEN r_Capacity - 1 
                                    ELSE r_Capacity 
                                END,
                                r_Availability = CASE 
                                    WHEN r_Capacity = 1 THEN 0 
                                    ELSE 1 
                                END 
                                WHERE r_ID = '$roomID'";

            if (mysqli_query($con, $updateRoomQuery)) {
                header("Location: tenants?status=accepted");
                exit();
            } else {
                die('Failed to update room: ' . mysqli_error($con));
            }
        } else {
            die('No room found for this tenant.');
        }
    } else {
        die('Query failed: ' . mysqli_error($con));
    }
} else {
    die('Invalid request.');
}

// Close database connection
mysqli_close($con);
