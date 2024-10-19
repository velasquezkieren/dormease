<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

// Only allow tenants (Account Type 1) to access this page
if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] != 1) {
    header("location: profile?u_ID=" . $_SESSION['u_ID']);
    die();
}

// Fetch tenant ID from session
$tenant_id = $_SESSION['u_ID'];

// Fetch booking details from the database
$query = "SELECT d.d_Name AS dormitory, r.r_Name AS room, d.d_Price AS price, o.o_Status
          FROM occupancy o
          JOIN room r ON o.o_Room = r.r_ID  -- Updated to join on r_ID
          JOIN dormitory d ON r.r_Dormitory = d.d_ID
          WHERE o.o_Occupant = '$tenant_id'";

$result = mysqli_query($con, $query);

// Check if the query was successful
if (!$result) {
    die('Query failed: ' . mysqli_error($con));
}

// Fetch the booking details
$bookings = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!-- HTML Section -->
<div class="container pt-5 min-vh-100" style="margin-top: 100px;">
    <div class="row">
        <!-- Sidebar nav -->
        <div class="col-md-4">
            <?php include('sidebar_profile.php'); ?>
        </div>

        <!-- Booking Details -->
        <div class="col-md-8">
            <h1 class="mb-4">Currently Booked at</h1>
            <?php if ($bookings): ?>
                <?php foreach ($bookings as $booking): ?>
                    <div class="card mb-3">
                        <div class="card-header bg-muted p-3 text-dark">
                            <p class="h4">Booking Details</p>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Dormitory: <?php echo htmlspecialchars($booking['dormitory']); ?></h5>
                            <p class="card-text">Room: <?php echo htmlspecialchars($booking['room']); ?></p>
                            <p class="card-text">Price: ₱<?php echo htmlspecialchars($booking['price']); ?></p>
                            <p class="card-text">
                                Status:
                                <?php
                                $statusBadge = '';
                                if ($booking['o_Status'] == 0) {
                                    $statusBadge = '<span class="badge bg-warning">Pending</span>';
                                } elseif ($booking['o_Status'] == 1) {
                                    $statusBadge = '<span class="badge bg-success">Accepted</span>';
                                } elseif ($booking['o_Status'] == 2) {
                                    $statusBadge = '<span class="badge bg-danger">Rejected</span>';
                                }
                                echo $statusBadge;
                                ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning mt-3">You currently have no bookings.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Close database connection
mysqli_close($con);
?>