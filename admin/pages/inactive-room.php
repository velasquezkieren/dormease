<?php

if (!isset($_SESSION["a_ID"])) {
    header("location:login");
    exit();
}

// Fetch all inactive rooms with r_RegistrationStatus = 0
$sql = "SELECT r.*, d.d_Name FROM room r
        JOIN dormitory d ON r.r_Dormitory = d.d_ID
        WHERE r.r_RegistrationStatus = 0"; // Assuming 0 indicates inactive

$inactiveRoomResult = $con->query($sql); // Replace $con with your database connection variable

// Initialize alert message variable
$alertMessage = '';

// Check if there's a query parameter for alert messages
if (isset($_GET['alert'])) {
    switch ($_GET['alert']) {
        case 'activated':
            $alertMessage = "<div class='alert alert-success'>Room activated successfully.</div>";
            break;
        case 'denied':
            $alertMessage = "<div class='alert alert-warning'>Room denied successfully.</div>";
            break;
        case 'error':
            $alertMessage = "<div class='alert alert-danger'>An error occurred. Please try again.</div>";
            break;
    }
}

// Handle form submission for activating or denying rooms
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $r_Name = $_POST['r_Name']; // Get room name from the form submission

        if ($_POST['action'] === 'activate') {
            // Activate the room
            $updateSql = "UPDATE room SET r_RegistrationStatus = 1 WHERE r_Name = ?";
            $stmt = $con->prepare($updateSql);
            $stmt->bind_param("s", $r_Name);
            if ($stmt->execute()) {
                header("Location: inactive-room?alert=activated"); // Redirect with alert
                exit();
            } else {
                header("Location: inactive-room?alert=error"); // Redirect with error
                exit();
            }
        } elseif ($_POST['action'] === 'deny') {
            // Deny the room
            $updateSql = "UPDATE room SET r_RegistrationStatus = 2 WHERE r_Name = ?";
            $stmt = $con->prepare($updateSql);
            $stmt->bind_param("s", $r_Name);
            if ($stmt->execute()) {
                header("Location: inactive-room?alert=denied"); // Redirect with alert
                exit();
            } else {
                header("Location: inactive-room?alert=error"); // Redirect with error
                exit();
            }
        }
    }
}
?>

<div class="container pt-5 mt-5">
    <div class="row mt-4">
        <div class="col-12">
            <h5>Inactive Rooms</h5>
            <?php if ($alertMessage): ?>
                <?php echo $alertMessage; // Display alert message 
                ?>
            <?php endif; ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Room Name</th>
                        <th>Dormitory</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($inactiveRoomResult->num_rows > 0): ?>
                        <?php while ($row = $inactiveRoomResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['r_Name']; ?></td>
                                <td><?php echo $row['d_Name']; ?></td>
                                <td><?php echo $row['r_Description']; ?></td>
                                <td>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="r_Name" value="<?php echo $row['r_Name']; ?>">
                                        <button type="submit" name="action" value="activate" class="btn btn-success">Activate</button>
                                        <button type="submit" name="action" value="deny" class="btn btn-danger">Deny</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No inactive rooms found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>