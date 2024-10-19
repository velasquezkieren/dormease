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

// Fetch tenants associated with the logged-in owner
$ownerID = $_SESSION['u_ID'];
$query = "SELECT u.u_ID, u.u_FName, u.u_LName, d.d_Name AS dormitory, r.r_Name AS room, 
                 d.d_Price AS price, o.o_Status, r.r_ID, r.r_Capacity AS capacity
          FROM occupancy o
          JOIN user u ON o.o_Occupant = u.u_ID
          JOIN room r ON o.o_Room = r.r_ID
          JOIN dormitory d ON r.r_Dormitory = d.d_ID
          WHERE d.d_Owner = '$ownerID'";

$result = mysqli_query($con, $query);

// Check if the query was successful
if (!$result) {
    die('Query failed: ' . mysqli_error($con));
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
            <h1>My Tenants</h1>
            <table class="table table-striped table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Tenant Name</th>
                        <th>Dormitory</th>
                        <th>Room Name</th>
                        <th>Price</th>
                        <th>Capacity</th> <!-- New column for capacity -->
                        <th>Status</th>
                        <th>Actions</th> <!-- Actions column -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Display the tenants
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['u_FName'] . ' ' . $row['u_LName']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['dormitory']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['room']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['price']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['capacity']) . '</td>'; // Display the room capacity
                        echo '<td>';

                        // Display the status
                        if ($row['o_Status'] == 0) {
                            echo '<span class="badge bg-warning">Pending</span>'; // Status when waiting for action
                        } elseif ($row['o_Status'] == 1) {
                            echo '<span class="badge bg-success">Accepted</span>';
                        } elseif ($row['o_Status'] == 2) {
                            echo '<span class="badge bg-warning">Rejected</span>';
                        }

                        echo '</td>'; // Close Status cell
                        echo '<td>'; // Start Actions cell

                        // Show buttons only if o_Status is 0 (Pending)
                        if ($row['o_Status'] == 0) {
                            echo '<a href="accept_tenant?id=' . htmlspecialchars($row['u_ID']) . '" class="btn btn-success">Accept</a>
                                  <a href="reject_tenant?id=' . htmlspecialchars($row['u_ID']) . '" class="btn btn-danger">Reject</a>';
                        } else {
                            echo '<a href="evict_tenant?id=' . htmlspecialchars($row['u_ID']) . '&room_id=' . htmlspecialchars($row['r_ID']) . '" class="btn btn-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-x-fill" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m6.146-2.854a.5.5 0 0 1 .708 0L14 6.293l1.146-1.147a.5.5 0 0 1 .708.708L14.707 7l1.147 1.146a.5.5 0 0 1-.708.708L14 7.707l-1.146 1.147a.5.5 0 0 1-.708-.708L13.293 7l-1.147-1.146a.5.5 0 0 1 0-.708"/>
</svg>
Evict</a>';
                        }

                        echo '</td>'; // Close Actions cell
                        echo '</tr>'; // Close table row
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Close database connection
mysqli_close($con);
?>