<!-- profile backup -->

<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login&auth-required");
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

// Prepare and execute the query
$stmt = $con->prepare($sql);
if (isset($email)) {
    $stmt->bind_param("s", $email);
} else {
    $stmt->bind_param("s", $user_ID);
}
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// Check if the user exists
if (!$data) {
    echo "<script>alert('User not found');</script>";
    exit();
}

// Display profile picture
$profile_pic = !empty($data['u_PicName']) ? 'upload/' . htmlspecialchars($data['u_PicName']) : 'upload/avatar.png';

$email = htmlspecialchars($data['u_Email']);
$contact_num = htmlspecialchars($data['u_ContactNumber']);
$lastname = htmlspecialchars($data['u_LName']);
$firstname = htmlspecialchars($data['u_FName']);

$fullname = ucwords($firstname) . " " . ucwords($lastname);

// Handle form submission for profile edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $error_code = '';

    // Sanitize input
    $new_firstname = ucwords(trim(mysqli_real_escape_string($con, $_POST['firstname'])));
    $new_lastname = ucwords(trim(mysqli_real_escape_string($con, $_POST['lastname'])));
    $new_contact_num = trim(mysqli_real_escape_string($con, $_POST['contact_num']));
    $new_email = !empty(trim($_POST['email'])) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : $email;
    $new_password = !empty(trim($_POST['password'])) ? $_POST['password'] : '';
    $confirm_email = !empty(trim($_POST['confirm_email'])) ? filter_var(trim($_POST['confirm_email']), FILTER_SANITIZE_EMAIL) : $new_email;
    $confirm_password = !empty(trim($_POST['confirm_password'])) ? $_POST['confirm_password'] : '';

    // Validate names
    $pattern_name = '/^[A-Za-zÀ-ÿ\s\'\-]+$/u';
    if (!preg_match($pattern_name, $new_firstname) || !preg_match($pattern_name, $new_lastname)) {
        $error_code = 'invalid-name';
    }

    // Validate email if provided
    if ($new_email !== $confirm_email) {
        $error_code = 'email-not-match';
    } elseif (!empty($new_email) && !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_code = 'invalid-email-format';
    }

    // Validate password if provided
    if (!empty($new_password) && ($new_password !== $confirm_password || !preg_match('/.{8,20}/', $new_password))) {
        $error_code = $error_code ?: 'pw-not-match';
    }

    // Validate contact number
    $pattern_contact = '/09\d{9}/';
    if (!preg_match($pattern_contact, $new_contact_num)) {
        $error_code = $error_code ?: 'invalid-contact';
    }

    // Check if email already exists
    if ($new_email !== $email) {
        $check_query = "SELECT 1 FROM user WHERE u_Email = ? AND u_ID != ?";
        $stmt = $con->prepare($check_query);
        $stmt->bind_param('ss', $new_email, $user_ID);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_code = $error_code ?: 'email-exists';
        }
    }

    // If there is any error, redirect to profile with the error code
    if ($error_code) {
        header("Location: profile?error=$error_code");
        exit();
    }

    // Build the update query dynamically
    $update_fields = [];
    $params = [];
    $param_types = '';

    if ($new_firstname !== $firstname) {
        $update_fields[] = 'u_FName = ?';
        $params[] = $new_firstname;
        $param_types .= 's';
    }
    if ($new_lastname !== $lastname) {
        $update_fields[] = 'u_LName = ?';
        $params[] = $new_lastname;
        $param_types .= 's';
    }
    if ($new_email !== $email) {
        $update_fields[] = 'u_Email = ?';
        $params[] = $new_email;
        $param_types .= 's';
    }
    if (!empty($new_password)) {
        $update_fields[] = 'u_Password = ?';
        $params[] = password_hash($new_password, PASSWORD_DEFAULT);
        $param_types .= 's';
    }
    if ($new_contact_num !== $contact_num) {
        $update_fields[] = 'u_ContactNumber = ?';
        $params[] = $new_contact_num;
        $param_types .= 's';
    }

    if (empty($update_fields)) {
        echo "<script>alert('No changes made.');</script>";
        exit();
    }

    $update_query = "UPDATE user SET " . implode(', ', $update_fields) . " WHERE u_ID = ?";
    $stmt = $con->prepare($update_query);
    $params[] = $user_ID;
    $param_types .= 's';
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Update session variables with new details
        $_SESSION['u_FName'] = $new_firstname;
        $_SESSION['u_LName'] = $new_lastname;
        echo "<script>alert('Profile updated successfully.');</script>";
        // Refresh the page to reflect changes
        header("location:profile?u_ID=" . $user_ID);
    } else {
        echo "<script>alert('No changes made or update failed.');</script>";
    }
}

// Handle account deletion
if (isset($_POST['delete'])) {
    $con->begin_transaction();
    try {
        // Delete user images from the server
        $image_query = $con->prepare("SELECT u_PicName FROM user WHERE u_ID = ?");
        $image_query->bind_param("s", $user_ID);
        $image_query->execute();
        $image_data = $image_query->get_result()->fetch_assoc();
        $picName = $image_data['u_PicName'];
        if ($picName) {
            $file_path = 'upload/' . $picName;
            if (file_exists($file_path)) {
                unlink($file_path); // Delete the profile picture
            }
        }

        // Delete all dormitory listings associated with the user
        $dorms_query = $con->prepare("SELECT d_PicName FROM dormitory WHERE d_Owner = ?");
        $dorms_query->bind_param("s", $user_ID);
        $dorms_query->execute();
        $dorms = $dorms_query->get_result();

        while ($dorm = $dorms->fetch_assoc()) {
            $dorm_images = explode(',', $dorm['d_PicName']);
            foreach ($dorm_images as $image) {
                $file_path = 'upload/' . $image;
                if (file_exists($file_path)) {
                    unlink($file_path); // Delete each dormitory image
                }
            }
        }

        // Delete dormitory listings
        $con->prepare("DELETE FROM dormitory WHERE d_Owner = ?")->execute([$user_ID]);

        // Delete user-related records in other tables
        $con->prepare("DELETE FROM ledger WHERE l_Biller = ? OR l_Recipient = ?")->execute([$user_ID, $user_ID]);
        $con->prepare("DELETE FROM occupancy WHERE o_Occupant = ?")->execute([$user_ID]);
        $con->prepare("DELETE FROM room WHERE r_Dormitory IN (SELECT d_ID FROM dormitory WHERE d_Owner = ?)")->execute([$user_ID]);

        // Delete the user
        $con->prepare("DELETE FROM user WHERE u_ID = ?")->execute([$user_ID]);

        $con->commit();

        // Logout after delete
        session_unset();
        session_destroy();
        header("Location: login");
        exit();
    } catch (Exception $e) {
        // Rollback if an error is encountered to retain data integrity
        $con->rollback();
        echo "<script>alert('Error deleting account: " . $e->getMessage() . "');</script>";
    }
}
?>

<div class="container pt-5">
    <div class="row pt-5">
        <div class="col-12 col-md-6 pt-5 d-flex justify-content-center justify-content-md-start">
            <img src="<?php echo $profile_pic; ?>" alt="<?php echo $fullname; ?>" class="img-fluid h-50 rounded-circle">
            <?php
            // Check if the logged-in user is viewing their own profile
            if (isset($_GET['u_ID']) && $_GET['u_ID'] != $_SESSION['u_ID']) {
                echo '<p class="h1 text-center text-md-left">' . $fullname . '</p>'; // Show the full name for another user's profile
            } else {
                echo '<p class="h1 text-center text-md-left">Welcome, ' . $fullname . '!</p>'; // Show the greeting if it's their own profile
            }
            ?>
        </div>
        <div class="col-12 col-md-6 pt-3 pt-md-5 d-flex justify-content-center justify-content-md-end align-items-center">
            <?php
            // Check if the user is viewing their own profile
            $isOwnProfile = !isset($_GET['u_ID']) || $_GET['u_ID'] == $_SESSION['u_ID'];

            if ($isOwnProfile) {
                // Display the "Edit Profile" button
                echo '<button type="button" class="btn login-button" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>';
            }
            ?>
        </div>
    </div>
    <?php
    // Display the "Create a listing" button if viewing own profile and account type is 0
    if ($isOwnProfile && isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 0) {
    ?>
        <div class="col-12 col-md-6 pt-3 pt-md-5 d-flex justify-content-center justify-content-md-end align-items-center">
            <a class="login-button" href="listing">Create a listing</a>
        </div>
    <?php
    }
    ?>
    <div class="row">
        <div class="col pt-2">
            <div class="col-12 col-md-6 pt-5 d-flex justify-content-center justify-content-md-start">
                <?php
                if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 0) {
                    echo '<p class="h2 pb-5">My Listings</p><br>';
                } else {
                    echo '<p class="h2 pb-5">Statement of Account</p>';
                }
                ?>
            </div>
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

<!-- Edit Profile Modal -->
<div class="modal" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm" method="POST" action="">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="firstname" placeholder="First Name" value="<?php echo $firstname; ?>" required pattern="^[A-Za-zÀ-ÿ\s\'\-]+">
                        <label for="firstname" class="form-label">First Name</label>
                    </div>
                    <div class="mb-3">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="lastname" placeholder="Last Name" value="<?php echo $lastname; ?>" required pattern="^[A-Za-zÀ-ÿ\s\'\-]+">
                            <label for="lastname" class="form-label">Last Name</label>
                        </div>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" name="email" placeholder="name@example.com" value="<?php echo $email; ?>">
                        <label for="email" class="form-label">Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" name="confirm_email" id="confirm_email" placeholder="name@example.com">
                        <label for="confirm_email" class="form-label">Confirm Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" name="password" placeholder="Password" id="password" minlength="8" maxlength="20" pattern=".{8,20}">
                        <label for="password" class="form-label">Password</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm Password" minlength="8" maxlength="20" pattern=".{8,20}">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="contact_num" placeholder="Contact Number" value="<?php echo $contact_num; ?>" required pattern="09\d{9}">
                        <label for="contact_num" class="form-label">Contact Number</label>
                    </div>
                    <!-- Add more fields as needed -->
                    <div class="modal-footer">
                        <!-- Button to trigger delete confirmation modal -->
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">Delete Account</button>
                        <button name="submit" class="btn btn-dark">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Delete Account Confirmation Modal -->
<div class="modal" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountModalLabel">Confirm Account Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete your account? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <!-- Confirm delete -->
                <form method="POST" action="">
                    <input type="hidden" name="u_ID" value="<?= htmlspecialchars($user_ID); ?>">
                    <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>