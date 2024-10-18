<?php

// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

// Check account type and redirect if necessary
if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] !== 0) {
    header("location: profile");
    die();
}

// Check if r_ID and d_ID are set in the URL
if (isset($_GET['r_ID']) && !empty($_GET['r_ID']) && isset($_GET['d_ID']) && !empty($_GET['d_ID'])) {
    $r_ID = $_GET['r_ID'];
    $d_ID = $_GET['d_ID'];

    // Check if the user owns the dormitory
    $ownerCheckSql = "SELECT COUNT(*) FROM dormitory WHERE d_ID = ? AND d_Owner = ?";
    $stmt = mysqli_prepare($con, $ownerCheckSql);
    mysqli_stmt_bind_param($stmt, 'ss', $d_ID, $_SESSION['u_ID']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $ownerCheck);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($ownerCheck == 0) {
        die("Error: You do not have permission to delete this room.");
    }

    // Prepare and execute the deletion of the room
    $deleteRoomSql = "DELETE FROM room WHERE r_ID = ?";
    $stmt = mysqli_prepare($con, $deleteRoomSql);
    mysqli_stmt_bind_param($stmt, 's', $r_ID);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Room deleted successfully!'); window.location.href='property?d_ID=$d_ID';</script>";
    } else {
        echo "Error: " . mysqli_error($con);
    }

    mysqli_stmt_close($stmt);
} else {
    header("location:find");
    die();
}
