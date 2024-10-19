<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

// Check if the user is logged in and has an account type of 1 or 2
if (!isset($_SESSION['u_Account_Type']) || ($_SESSION['u_Account_Type'] != 1 && $_SESSION['u_Account_Type'] != 2)) {
    header('location:profile');
    exit();
}


// Get user ID from the session
$user_ID = $_SESSION['u_ID'];

// Fetch Visit Table Data
$query = "SELECT v.v_DateTime, 
                 CONCAT(u.u_FName, ' ', u.u_MName, ' ', u.u_LName) AS landlord_name,
                 d.d_Name AS dorm_name,
                 d.d_ID AS dorm_ID, -- Add dormitory ID to the query
                 v.v_Status
          FROM visit v 
          JOIN user u ON v.v_Landlord = u.u_ID
          JOIN dormitory d ON v.v_Dormitory = d.d_ID
          WHERE v.v_Visitor = ?";

$stmt = $con->prepare($query);
$stmt->bind_param("s", $user_ID);
$stmt->execute();
$result = $stmt->get_result();
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
            <h1 class="mb-4">Scheduled Visits</h1>
            <?php
            if (isset($_GET['schedule-success'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Schedule successfully created!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
            }
            ?>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Landlord</th>
                        <th>Dormitory</th>
                        <th>Status</th>
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
                                    $statusClass = "badge bg-warning"; // Yellow color for pending
                                    break;
                                case 1:
                                    $statusText = "Accepted";
                                    $statusClass = "badge bg-success"; // Green color for accepted
                                    break;
                                case 0:
                                    $statusText = "Rejected";
                                    $statusClass = "badge bg-danger"; // Red color for rejected
                                    break;
                                default:
                                    $statusText = "Unknown";
                                    $statusClass = "badge bg-secondary"; // Grey for unknown status
                            }

                            // Anchor the dormitory name with a link to the dorm details page
                            $dormLink = 'property?d_ID=' . urlencode($row['dorm_ID']); // Use the correct dorm_ID
                            echo "<tr>
                                <td>" . htmlspecialchars($formattedDateTime) . "</td>
                                <td>" . htmlspecialchars($row['landlord_name']) . "</td>
                                <td><a href='" . htmlspecialchars($dormLink) . "'>" . htmlspecialchars($row['dorm_name']) . "</a></td>
                                <td><span class='" . $statusClass . "'>" . htmlspecialchars($statusText) . "</span></td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>No scheduled visits found.</td></tr>";
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