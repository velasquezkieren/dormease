<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] !== 0) {
    header("location: home");
    die();
}

// Fetch the logged-in user's ID for comparison
$user_ID = $_SESSION['u_ID'];

// Prepare and execute query for dormitories with owner's name and registration status
$stmt = $con->prepare("
    SELECT d.*, u.u_FName, u.u_MName, u.u_LName, u.u_Picture 
    FROM dormitory d 
    JOIN user u ON d.d_Owner = u.u_ID 
    WHERE d.d_Owner = ?
");
$stmt->bind_param("s", $user_ID);
$stmt->execute();
$dorms_query = $stmt->get_result();

// Categorize the dorms based on registration status
$pending_dorms = [];
$active_dorms = [];
$rejected_dorms = [];

while ($dorm = $dorms_query->fetch_assoc()) {
    if ($dorm['d_RegistrationStatus'] == 0) {
        $pending_dorms[] = $dorm;
    } elseif ($dorm['d_RegistrationStatus'] == 1) {
        $active_dorms[] = $dorm;
    } elseif ($dorm['d_RegistrationStatus'] == 2) {
        $rejected_dorms[] = $dorm;
    }
}

$stmt->close();
?>

<!-- HTML Section -->
<div class="container pt-5 min-vh-100" style="margin-top: 100px;">
    <div class="row">

        <!-- Sidebar -->
        <div class="col-md-4">
            <?php include('sidebar_profile.php'); ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-8">
            <h1 class="mb-4">My Listings</h1>

            <!-- Nav Tabs -->
            <ul class="nav" id="dormTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="true">
                        Pending Dorms (<?= count($pending_dorms); ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab" aria-controls="active" aria-selected="false">
                        Active Dorms (<?= count($active_dorms); ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab" aria-controls="rejected" aria-selected="false">
                        Rejected Dorms (<?= count($rejected_dorms); ?>)
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="dormTabsContent">
                <!-- Pending Dorms Tab -->
                <div class="tab-pane show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                    <div class="row mt-3">
                        <?php if (!empty($pending_dorms)): ?>
                            <?php foreach ($pending_dorms as $dorm): ?>
                                <?php include('dorm-card.php'); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-secondary text-center mx-auto p-4" role="alert">
                                No pending listings.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Active Dorms Tab -->
                <div class="tab-pane" id="active" role="tabpanel" aria-labelledby="active-tab">
                    <div class="row mt-3">
                        <?php if (!empty($active_dorms)): ?>
                            <?php foreach ($active_dorms as $dorm): ?>
                                <?php include('dorm-card.php'); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-secondary text-center mx-auto p-4" role="alert">
                                No active listings.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Rejected Dorms Tab -->
                <div class="tab-pane" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
                    <div class="row mt-3">
                        <?php if (!empty($rejected_dorms)): ?>
                            <?php foreach ($rejected_dorms as $dorm): ?>
                                <?php include('dorm-card.php'); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-secondary text-center mx-auto p-4" role="alert">
                                No rejected listings.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$con->close(); // Close the database connection
?>