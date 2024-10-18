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

// Check if d_ID is set in the URL
if (isset($_GET['d_ID']) && !empty($_GET['d_ID'])) {
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
        die("Error: You do not have permission to add a room to this dormitory.");
    }

    // Retrieve the dormitory name
    $dormNameSql = "SELECT d_Name FROM dormitory WHERE d_ID = ?";
    $stmt = mysqli_prepare($con, $dormNameSql);
    mysqli_stmt_bind_param($stmt, 's', $d_ID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $dormName);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
} else {
    header("location:find");
    die();
}

// Handle form submission for adding a room
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Generate unique room ID
    $r_ID = uniqid('room_');

    // Collect form data
    $r_Name = ucwords($_POST['r_Name']);
    $r_Description = trim($_POST['r_Description']);
    $r_Availability = isset($_POST['r_Availability']) ? 1 : 0; // Checkbox value
    $r_Capacity = intval($_POST['r_Capacity']);
    $r_RegistrationStatus = 0; // Default value for registration status

    // Validate input
    if (empty($r_Name) || empty($r_Description) || $r_Capacity <= 0) {
        echo "<script>alert('Please fill in all fields correctly.'); history.back();</script>";
        exit;
    }

    // Insert room into the database using prepared statement
    $insertRoomSql = "INSERT INTO room (r_ID, r_Name, r_Description, r_Availability, r_Capacity, r_Dormitory, r_RegistrationStatus) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($con, $insertRoomSql);
    mysqli_stmt_bind_param($stmt, 'sssiisi', $r_ID, $r_Name, $r_Description, $r_Availability, $r_Capacity, $d_ID, $r_RegistrationStatus);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Room added successfully!'); window.location.href='property?d_ID=$d_ID';</script>";
    } else {
        echo "Error: " . mysqli_error($con);
    }

    mysqli_stmt_close($stmt);
}
?>

<!-- Form Section -->
<div class="container pt-5 mt-5">
    <div class="row pt-5">
        <h2>Adding Room for <?php echo htmlspecialchars($dormName); ?></h2>
        <form action="" method="POST">
            <div class="form-group">
                <label for="r_Name">Room Name</label>
                <input type="text" name="r_Name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="r_Description">Description</label>
                <textarea name="r_Description" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label for="r_Availability">Available</label>
                <input type="checkbox" name="r_Availability" value="1" class="form-check-input">
                <label class="form-check-label" for="r_Availability">Check if available</label>
            </div>
            <div class="form-group">
                <label for="r_Capacity">Capacity</label>
                <input type="number" name="r_Capacity" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-dark">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-add-fill" viewBox="0 0 16 16">
                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 1 1-1 0v-1h-1a.5.5 0 1 1 0-1h1v-1a.5.5 0 0 1 1 0" />
                    <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293z" />
                    <path d="m8 3.293 4.712 4.712A4.5 4.5 0 0 0 8.758 15H3.5A1.5 1.5 0 0 1 2 13.5V9.293z" />
                </svg>
                Add Room
            </button>
            <a href="property?d_ID=<?= $d_ID; ?>" class="btn btn-secondary">Back to Property</a>
        </form>
    </div>
</div>