<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login&auth-required");
    die();
}

// Check if u_ID is provided in the URL
$user_ID = $_SESSION['u_ID']; // Store the logged-in user's ID for comparison
$sql = "SELECT * FROM user WHERE u_ID = ?";

if (isset($_GET['u_ID'])) {
    $user_ID = $_GET['u_ID'];
}

// Prepare and execute the query
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $user_ID);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// Check if the user exists
if (!$data) {
    echo "<script>alert('User not found');</script>";
    exit();
}

$profile_pic = !empty($data['u_PicName']) && file_exists('upload/' . htmlspecialchars($data['u_PicName']))
    ? 'upload/' . htmlspecialchars($data['u_PicName'])
    : 'user_avatar/default_avatar.png';

$email = htmlspecialchars($data['u_Email']);
$contact_num = htmlspecialchars($data['u_ContactNumber']);
$lastname = htmlspecialchars($data['u_LName']);
$firstname = htmlspecialchars($data['u_FName']);
$account_type = htmlspecialchars($data['u_Account_Type']);
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

<!-- HTML Section -->
<div class="container pt-5" style="margin-top: 100px;"> <!-- Adjust margin for fixed navbar -->
    <div class="row">

        <!-- Sidebar -->
        <div class="col-md-4">
            <?php include('sidebar_profile.php'); ?>
        </div>

        <!-- Main Profile Content -->
        <div class="col-md-8">
            <div class="d-flex flex-column align-items-center text-center">
                <a href="#">
                    <!-- profile picture here -->
                    <img src="<?php echo $profile_pic; ?>" alt="<?php echo $fullname; ?>" class="img-fluid mb-3 rounded-circle">
                </a>
                <?php if (!isset($_GET['u_ID']) || $_GET['u_ID'] == $_SESSION['u_ID']): ?>
                    <button type="button" class="btn btn-outline-secondary mb-2 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#changeProfilePicModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-image me-2" viewBox="0 0 16 16">
                            <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0" />
                            <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1z" />
                        </svg>
                        Change Profile Picture
                    </button>
                    <button type="button" class="btn text-reset" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill me-2" viewBox="0 0 16 16">
                            <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z" />
                        </svg>
                        Edit Profile Details
                    </button>
                <?php endif; ?>
                <p class="h1 mb-0"><?php echo $fullname; ?></p>
            </div>

            <!-- User Details Table -->
            <div class="mt-4">
                <table class="table">
                    <tbody>
                        <tr>
                            <th>Name</th>
                            <td><?php echo htmlspecialchars($fullname); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo $email; ?></td>
                        </tr>
                        <tr>
                            <th>Contact Number</th>
                            <td><?php echo $contact_num; ?></td>
                        </tr>
                        <tr>
                            <th>Account Type</th>
                            <td>
                                <?php
                                if (isset($_SESSION['u_Account_Type']) && ($_SESSION['u_Account_Type'] == 0)) {
                                    echo 'Owner';
                                } else if (isset($_SESSION['u_Account_Type']) && ($_SESSION['u_Account_Type'] == 1)) {
                                    echo 'Tenant';
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
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
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0" />
                            </svg>
                            Delete Account
                        </button>
                        <button name="submit" class="btn btn-dark">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy-fill" viewBox="0 0 16 16">
                                <path d="M0 1.5A1.5 1.5 0 0 1 1.5 0H3v5.5A1.5 1.5 0 0 0 4.5 7h7A1.5 1.5 0 0 0 13 5.5V0h.086a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5H14v-5.5A1.5 1.5 0 0 0 12.5 9h-9A1.5 1.5 0 0 0 2 10.5V16h-.5A1.5 1.5 0 0 1 0 14.5z" />
                                <path d="M3 16h10v-5.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5zm9-16H4v5.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5zM9 1h2v4H9z" />
                            </svg>
                            Save changes
                        </button>
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
                    <button type="submit" name="delete" class="btn btn-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                            <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0" />
                        </svg>
                        Delete
                    </button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>