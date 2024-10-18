<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

// Check if the user is logged in and has an account type of 0 (assuming 0 is for owners)
if (!isset($_SESSION['u_Account_Type']) || $_SESSION['u_Account_Type'] != 0) {
    header('location:profile');
    exit();
}

// Get user ID from the session (landlord's ID)
$landlord_ID = $_SESSION['u_ID'];

// Fetch Visit Table Data for the owner's properties
$query = "SELECT v.v_ID, v.v_DateTime, 
                 CONCAT(u.u_FName, ' ', u.u_MName, ' ', u.u_LName) AS tenant_name,
                 d.d_Name AS dorm_name,
                 d.d_ID AS dorm_ID, -- Add dormitory ID to the query
                 v.v_Status
          FROM visit v 
          JOIN user u ON v.v_Visitor = u.u_ID
          JOIN dormitory d ON v.v_Dormitory = d.d_ID
          WHERE v.v_Landlord = ?";

$stmt = $con->prepare($query);
$stmt->bind_param("s", $landlord_ID);
$stmt->execute();
$result = $stmt->get_result();

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $visitID = $_POST['v_ID'];
    $action = $_POST['action'];

    // Determine the status to set based on the action
    if ($action === 'accept') {
        $newStatus = 1; // Accepted
    } elseif ($action === 'reject') {
        $newStatus = 0; // Rejected
    } else {
        // Invalid action
        header("location:visit-schedules?error=invalid-action");
        exit();
    }

    // Update the visit status in the database
    $updateQuery = "UPDATE visit SET v_Status = ? WHERE v_ID = ?";
    $updateStmt = $con->prepare($updateQuery);
    $updateStmt->bind_param("is", $newStatus, $visitID);

    if ($updateStmt->execute()) {
        // Redirect back to the visits page with success message
        if ($newStatus === 1) {
            header("location:visit-schedules?schedule-success=accepted");
        } elseif ($newStatus === 0) {
            header("location:visit-schedules?schedule-success=rejected");
        }
    } else {
        // Redirect back to the visits page with an error message
        header("location:visit-schedules?error=update-failed");
    }

    $updateStmt->close();
}
?>

<!-- HTML Section -->
<div class="container pt-5 min-vh-100" style="margin-top: 100px;"> <!-- Adjust margin for fixed navbar -->
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-4">
            <?php
            // Show this only if the user is viewing their own profile
            if (!isset($_GET['u_ID']) || $_GET['u_ID'] == $_SESSION['u_ID']) {
                include('sidebar_profile.php');
            }
            ?>
        </div>
        <div class="col-md-8">
            <h1>Tenant Scheduled Visits</h1>
            <?php
            // Display success or error messages
            if (isset($_GET['schedule-success'])) {
                if ($_GET['schedule-success'] == 'accepted') {
                    echo '<div class="alert alert-success">Visit has been accepted successfully!</div>';
                } elseif ($_GET['schedule-success'] == 'rejected') {
                    echo '<div class="alert alert-danger">Visit has been rejected successfully!</div>';
                }
            }
            ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Tenant</th>
                        <th>Dormitory</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Display the scheduled visits
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Format the datetime
                            $dateTime = new DateTime($row['v_DateTime']);
                            $formattedDateTime = $dateTime->format('d/m/Y, h:i A'); // Format as DD/MM/YYYY, HH:MM AM/PM

                            // Determine status and corresponding color
                            switch ($row['v_Status']) {
                                case 2:
                                    $statusText = "Pending";
                                    $statusClass = "text-warning"; // Yellow color for pending
                                    break;
                                case 1:
                                    $statusText = "Accepted";
                                    $statusClass = "text-success"; // Green color for accepted
                                    break;
                                case 0:
                                    $statusText = "Rejected";
                                    $statusClass = "text-danger"; // Red color for rejected
                                    break;
                                default:
                                    $statusText = "Unknown";
                                    $statusClass = "text-secondary"; // Grey for unknown status
                            }

                            // Anchor the dormitory name with a link to the dorm details page
                            $dormLink = 'property?d_ID=' . urlencode($row['dorm_ID']); // Use the correct dorm_ID

                            // Start creating the table row
                            echo "<tr>
                <td>" . htmlspecialchars($formattedDateTime) . "</td>
                <td>" . htmlspecialchars($row['tenant_name']) . "</td>
                <td><a href='" . htmlspecialchars($dormLink) . "'>" . htmlspecialchars($row['dorm_name']) . "</a></td>
                <td class='" . $statusClass . "'>" . htmlspecialchars($statusText) . "</td>
                <td>";

                            // Show buttons only if the visit status is Pending (2)
                            if ($row['v_Status'] == 2) {
                                echo "<form method='post' action=''>
                    <input type='hidden' name='v_ID' value='" . htmlspecialchars($row['v_ID']) . "'>
                    <button type='submit' name='action' value='accept' class='btn btn-success btn-sm'>Accept</button>
                    <button type='submit' name='action' value='reject' class='btn btn-danger btn-sm'>Reject</button>
                </form>";
                            } else {
                                echo ""; // Indicate no action available for non-pending statuses
                            }

                            echo "</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No scheduled visits found.</td></tr>";
                    }
                    ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

<?php
// Close the database connection
$stmt->close();
$con->close();
?>