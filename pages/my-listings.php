<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login&auth-required");
    die();
}

if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] !== 0) {
    header("location: home");
    die();
}

// Check if u_ID is provided in the URL
if (isset($_GET['u_ID'])) {
    $user_ID = $_GET['u_ID'];
    $sql = "SELECT * FROM user WHERE u_ID = ?";
} else {
    // Fetch the logged-in user's profile
    $email = $_SESSION['u_Email'];
    $sql = "SELECT * FROM user WHERE u_Email = ?";
    $user_ID = $_SESSION['u_ID']; // Store the logged-in user's ID for comparison
}
?>

<!-- HTML Section -->
<div class="container pt-5" style="margin-top: 100px;">
    <div class="row">

        <!-- Sidebar -->
        <div class="col-md-4">
            <?php include('sidebar_profile.php'); ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-8">
            <h1>My Listings</h1>
            <div class="row">
                <?php
                // Retrieve the user ID from the query string
                $profile_ID = isset($_GET['u_ID']) ? mysqli_real_escape_string($con, $_GET['u_ID']) : $_SESSION['u_ID'];

                // Check if the user ID is valid and fetch dormitories
                $sql = "SELECT * FROM dormitory WHERE d_Owner = '$profile_ID'";
                $dorms_query = mysqli_query($con, $sql);

                if (mysqli_num_rows($dorms_query) > 0):
                    // Display dorm listings as cards
                    while ($dorm = mysqli_fetch_assoc($dorms_query)):
                        // Fetch the owner's name
                        $owner_ID = mysqli_real_escape_string($con, $dorm['d_Owner']);
                        $owner_query = mysqli_query($con, "SELECT u_FName, u_LName FROM user WHERE u_ID = '$owner_ID'");
                        $owner_data = mysqli_fetch_assoc($owner_query);
                        $owner_name = $owner_data ? htmlspecialchars($owner_data['u_FName'] . ' ' . $owner_data['u_LName']) : 'Unknown';

                        // Get the image names and use the first image for the card
                        $images = explode(',', $dorm['d_PicName']);
                        $first_image = $images[0];

                        // Limit the description to 100 characters
                        $description = substr($dorm['d_Description'], 0, 100);
                        if (strlen($dorm['d_Description']) > 100) {
                            $description .= '...';
                        }
                ?>
                        <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
                            <a href="property?d_ID=<?= urlencode($dorm['d_ID']); ?>" class="text-decoration-none">
                                <div class="card h-100 border-1">
                                    <div class="card-img-container">
                                        <img src="upload/<?= htmlspecialchars($dorm['d_ID'] . '/' . $first_image); ?>" class="card-img-top" alt="<?= htmlspecialchars($dorm['d_Name']); ?>">

                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?= htmlspecialchars($dorm['d_Name']); ?></h5>
                                        <p class="card-text text-truncate" style="max-height: 3.6em; overflow: hidden;"><?= htmlspecialchars($description); ?></p>
                                        <p class="card-text"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($dorm['d_Street']) . ', ' . htmlspecialchars($dorm['d_City']); ?></p>
                                        <p class="card-text"><strong>Owner:</strong> <?= htmlspecialchars($owner_name); ?></p>
                                        <span class="btn btn-dark mt-auto">View Details</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php
                    endwhile;
                else:
                    ?>
                    <div class="alert alert-secondary text-center mx-auto p-5" role="alert">
                        No listings available at the moment
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>