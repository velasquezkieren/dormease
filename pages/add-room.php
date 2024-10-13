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
    $ownerCheckSql = "SELECT COUNT(*) FROM dormitory WHERE d_ID = '$d_ID' AND d_Owner = '{$_SESSION['u_ID']}'";
    $ownerCheckResult = mysqli_query($con, $ownerCheckSql);
    $ownerCheck = mysqli_fetch_array($ownerCheckResult)[0];

    if ($ownerCheck == 0) {
        die("Error: You do not have permission to add a room to this dormitory.");
    }

    // Retrieve the dormitory name
    $dormNameSql = "SELECT d_Name FROM dormitory WHERE d_ID = '$d_ID'";
    $dormNameResult = mysqli_query($con, $dormNameSql);
    $dormName = mysqli_fetch_array($dormNameResult)['d_Name'];
} else {
    header("location:find");
    die();
}

// Handle form submission for adding a room
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $r_Name = mysqli_real_escape_string($con, trim($_POST['r_Name']));
    $r_Description = mysqli_real_escape_string($con, trim($_POST['r_Description']));
    $r_Availability = isset($_POST['r_Availability']) ? 1 : 0; // Checkbox value
    $r_Capacity = intval($_POST['r_Capacity']);
    $r_RegistrationStatus = 0; // Default value for registration status

    // Insert room into the database
    $insertRoomSql = "INSERT INTO room (r_Name, r_Description, r_Availability, r_Capacity, r_Dormitory, r_RegistrationStatus) 
                      VALUES ('$r_Name', '$r_Description', $r_Availability, $r_Capacity, '$d_ID', $r_RegistrationStatus)";

    if (mysqli_query($con, $insertRoomSql)) {
        echo "<script>alert('Room added successfully!'); window.location.href='property?d_ID=$d_ID';</script>";
    } else {
        echo "Error: " . mysqli_error($con);
    }
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
            <button type="submit" class="btn btn-success">Add Room</button>
        </form>
    </div>
</div>