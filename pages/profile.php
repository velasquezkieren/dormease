<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
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

$profile_pic = htmlspecialchars($data['u_Picture']);
$email = htmlspecialchars($data['u_Email']);
$contact_num = htmlspecialchars($data['u_ContactNumber']);
$lastname = htmlspecialchars($data['u_LName']);
$firstname = htmlspecialchars($data['u_FName']);
$middlename = htmlspecialchars($data['u_MName']);
$account_type = htmlspecialchars($data['u_Account_Type']);
$fullname = ucwords($firstname) . " " . ucwords($middlename) . " " . ucwords($lastname);

// Handle form submission for profile edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editDetails'])) {
    $error_code = '';

    // Sanitize input
    $new_firstname = ucwords(trim(mysqli_real_escape_string($con, $_POST['firstname'])));
    $new_middlename = ucwords(trim(mysqli_real_escape_string($con, $_POST['middlename']))); // This can be empty now
    $new_lastname = ucwords(trim(mysqli_real_escape_string($con, $_POST['lastname'])));
    $new_contact_num = trim(mysqli_real_escape_string($con, $_POST['contact_num']));
    $new_email = !empty(trim($_POST['email'])) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : $email;
    $new_password = !empty(trim($_POST['password'])) ? $_POST['password'] : '';
    $confirm_email = !empty(trim($_POST['confirm_email'])) ? filter_var(trim($_POST['confirm_email']), FILTER_SANITIZE_EMAIL) : $new_email;
    $confirm_password = !empty(trim($_POST['confirm_password'])) ? $_POST['confirm_password'] : '';

    // Validate names
    $pattern_name = '/^[\p{L}\'\s\-]*$/u'; // Allow empty for middle name
    if (
        !preg_match($pattern_name, $new_firstname) || !preg_match($pattern_name, $new_lastname) ||
        (!empty($new_middlename) && !preg_match($pattern_name, $new_middlename))
    ) {
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
    if ($new_middlename !== $middlename) {
        $update_fields[] = 'u_MName = ?';
        $params[] = $new_middlename ?: null; // Use NULL if empty
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
        header("location:profile?u_ID=" . $user_ID);
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
        $_SESSION['u_MName'] = $new_middlename;
        $_SESSION['u_LName'] = $new_lastname;
        echo "<script>alert('Profile updated successfully.');</script>";
        // Refresh the page to reflect changes
        header("location:profile?u_ID=" . $user_ID);
    } else {
        echo "<script>alert('No changes made or update failed.');</script>";
    }
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $con->begin_transaction();
    try {
        // Delete user images from the server
        $image_query = $con->prepare("SELECT u_Picture FROM user WHERE u_ID = ?");
        $image_query->bind_param("s", $user_ID);
        $image_query->execute();
        $image_data = $image_query->get_result()->fetch_assoc();
        $picName = $image_data['u_Picture'];

        // Check if the profile picture is not the default avatar before attempting to delete
        if ($picName && $picName !== 'default_avatar.png') {
            $file_path = 'user_avatar/' . $picName;
            if (file_exists($file_path) && !unlink($file_path)) {
                throw new Exception("Failed to delete profile picture.");
            }
        }

        // Get verification picture for deletion
        $verify_query = $con->prepare("SELECT u_VerificationPicture FROM user WHERE u_ID = ?");
        $verify_query->bind_param("s", $user_ID);
        $verify_query->execute();
        $verify_data = $verify_query->get_result()->fetch_assoc();
        $verifyPic = $verify_data['u_VerificationPicture'];

        if ($verifyPic) {
            $file_path = 'upload_verify/' . $user_ID . '/' . $verifyPic;
            if (file_exists($file_path)) {
                if (!unlink($file_path)) {
                    throw new Exception("Failed to delete verification picture.");
                }
            }

            // Check if the directory is empty and delete it
            $directoryPath = 'upload_verify/' . $user_ID . '/';
            if (is_dir($directoryPath) && count(scandir($directoryPath)) == 2) { // 2 means only . and ..
                if (!rmdir($directoryPath)) {
                    throw new Exception("Failed to delete directory: " . $directoryPath);
                }
            }
        }

        // Delete all dormitory listings associated with the user
        $dorms_query = $con->prepare("SELECT d_ID, d_PicName FROM dormitory WHERE d_Owner = ?");
        $dorms_query->bind_param("s", $user_ID);
        $dorms_query->execute();
        $dorms = $dorms_query->get_result();

        while ($dorm = $dorms->fetch_assoc()) {
            $dorm_ID = $dorm['d_ID'];
            $dorm_images = explode(',', $dorm['d_PicName']);
            $directoryPath = 'upload/' . $dorm_ID . '/'; // Use the directory based on dormitory ID

            // Delete images from the server
            foreach ($dorm_images as $image) {
                $file_path = $directoryPath . $image; // Correct the path to include the directory
                if (file_exists($file_path) && !unlink($file_path)) {
                    throw new Exception("Failed to delete dormitory image: " . $file_path);
                }
            }

            // Optionally, remove the directory if empty after deletion
            if (is_dir($directoryPath) && count(scandir($directoryPath)) == 2) { // Check if directory is empty
                rmdir($directoryPath); // Remove the empty directory
            }
        }

        // Delete dormitory listings
        $delete_dorm_query = $con->prepare("DELETE FROM dormitory WHERE d_Owner = ?");
        $delete_dorm_query->bind_param("s", $user_ID);
        $delete_dorm_query->execute();

        // Delete user-related records in other tables
        $delete_ledger_query = $con->prepare("DELETE FROM ledger WHERE l_Biller = ? OR l_Recipient = ?");
        $delete_ledger_query->bind_param("ss", $user_ID, $user_ID);
        $delete_ledger_query->execute();

        $delete_occupancy_query = $con->prepare("DELETE FROM occupancy WHERE o_Occupant = ?");
        $delete_occupancy_query->bind_param("s", $user_ID);
        $delete_occupancy_query->execute();

        $delete_room_query = $con->prepare("DELETE FROM room WHERE r_Dormitory IN (SELECT d_ID FROM dormitory WHERE d_Owner = ?)");
        $delete_room_query->bind_param("s", $user_ID);
        $delete_room_query->execute();

        // Delete the user
        $delete_user_query = $con->prepare("DELETE FROM user WHERE u_ID = ?");
        $delete_user_query->bind_param("s", $user_ID);
        $delete_user_query->execute();

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

if (isset($_POST['changeProfilePic'])) {
    // Allowed file types
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxFileSize = 5 * 1024 * 1024; // Maximum file size: 5MB

    // Directory for storing uploaded files
    $uploadDir = "user_avatar/";

    // Handle the uploaded file
    $file = $_FILES['profile_pic'];

    // Check if a file was uploaded
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileName = basename($file['name']);
        $fileType = $file['type'];
        $fileSize = $file['size'];

        // Validate file type and size
        if (!in_array($fileType, $allowedTypes)) {
            echo '<script>alert("Invalid file type. Please upload an image in the allowed formats.");</script>';
            exit();
        }
        if ($fileSize > $maxFileSize) {
            echo '<script>alert("File size too large. Maximum file size is 5MB.");</script>';
            exit();
        }

        // Generate a unique name for the uploaded file to avoid overwriting
        $uniqueFileName = uniqid($user_ID . "-") . '@dormease@' . $fileName;

        // Define the file path
        $filePath = $uploadDir . $uniqueFileName;

        // Move the file to the specified directory
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Update user account with new profile picture
            $updateStmt = $con->prepare("UPDATE user SET u_Picture = ? WHERE u_ID = ?");
            $updateStmt->bind_param('ss', $uniqueFileName, $user_ID);

            if ($updateStmt->execute()) {
                echo '<script>alert("Profile picture updated successfully.");</script>';
                header('Location: profile?edit-picture-success');
                exit();
            } else {
                echo '<script>alert("Error updating profile picture.");</script>';
            }
        } else {
            echo '<script>alert("Error uploading file.");</script>';
        }
    } else {
        echo '<script>alert("No file uploaded or there was an error.");</script>';
    }
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

        <!-- Main Profile Content -->
        <div class="col-md-8">
            <?php
            if (isset($_GET['application-success'])) {
                echo '<div class="alert alert-success" role="alert">
                    Application Success!
                </div>';
            } elseif (isset($_GET['edit-picture-success'])) {
                echo '<div class="alert alert-success" role="alert">
                    Updated Profile Picture
                </div>';
            }
            ?>
            <div class="d-flex flex-column align-items-center text-center">
                <!-- Profile picture here -->
                <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#changeProfilePicModal">
                    <img src="./user_avatar/<?php echo htmlspecialchars($profile_pic); ?>" alt="<?php echo htmlspecialchars($fullname); ?>" class="img-fluid rounded-circle" style="width: 300px; height: 300px; object-fit: cover;">
                </button>

                <?php if (!isset($_GET['u_ID']) || $_GET['u_ID'] == $_SESSION['u_ID']): ?>
                    <!-- Change Profile Picture Button -->
                    <button type="button" class="btn btn-outline-secondary mb-2 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#changeProfilePicModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-image me-2" viewBox="0 0 16 16">
                            <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0" />
                            <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1z" />
                        </svg>
                        Change Profile Picture
                    </button>

                    <!-- Edit Profile Details Button -->
                    <button type="button" class="btn text-reset" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill me-2" viewBox="0 0 16 16">
                            <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z" />
                        </svg>
                        Edit Profile Details
                    </button>
                <?php endif; ?>
                <!-- Full name -->
                <p class="h1 mb-0"><?php echo htmlspecialchars($fullname); ?></p>
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
                            <td><?php echo htmlspecialchars($email); ?></td>
                        </tr>
                        <tr>
                            <th>Contact Number</th>
                            <td><?php echo htmlspecialchars($contact_num); ?></td>
                        </tr>
                        <tr>
                            <th>Account Type</th>
                            <td>
                                <?php
                                // Check if viewing own profile
                                if (!isset($_GET['u_ID']) || $_GET['u_ID'] == $_SESSION['u_ID']) {
                                    // Display logged-in user's account type
                                    if ($_SESSION['u_Account_Type'] == 0) {
                                        echo 'Owner';
                                    } elseif ($_SESSION['u_Account_Type'] == 1) {
                                        echo 'Tenant';
                                    } else {
                                        echo 'Application Pending'; // Added for pending applications
                                    }
                                } else {
                                    // Fetch and display the account type of the user being viewed
                                    $u_ID = $_GET['u_ID'];
                                    $query = "SELECT u_Account_Type FROM user WHERE u_ID = ?";
                                    $stmt = $con->prepare($query);
                                    $stmt->bind_param('s', $u_ID);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        if ($row['u_Account_Type'] == 0) {
                                            echo 'Owner';
                                        } elseif ($row['u_Account_Type'] == 1) {
                                            echo 'Tenant';
                                        } else {
                                            echo 'Application Pending'; // Added for pending applications
                                        }
                                    } else {
                                        echo 'Unknown';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        <?php if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 1):
                            if (!isset($_GET['u_ID']) || $_GET['u_ID'] == $_SESSION['u_ID']):
                        ?>
                                <tr>
                                    <th></th>
                                    <td>
                                        Do you want to list your property?
                                        <a href="application?u_ID=<?php echo htmlspecialchars($user_ID); ?>" class="text-secondary">
                                            Apply as Owner
                                        </a>
                                    </td>
                                </tr>
                        <?php endif;
                        endif; ?>
                    </tbody>
                </table>
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
                <form id="editProfileForm" method="POST">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="firstname" placeholder="First Name" value="<?php echo $firstname; ?>" required pattern="^[\p{L}'\s\-]+$" minlength="2">
                        <label for="firstname" class="form-label">First Name</label>
                    </div>
                    <div class="mb-3">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="middlename" placeholder="Middle Name" value="<?php echo $middlename; ?>" pattern="^[\p{L}'\s\-]+$" minlength="2">
                            <label for="middlename" class="form-label">Middle Name</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="lastname" placeholder="Last Name" value="<?php echo $lastname; ?>" required pattern="^[\p{L}'\s\-]+$" minlength="2">
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
                        <button name="editDetails" class="btn btn-dark">
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

<!-- Modal for Changing Profile Picture -->
<div class="modal" id="changeProfilePicModal" tabindex="-1" aria-labelledby="changeProfilePicModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeProfilePicModalLabel">Change Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($profile_pic_success)): ?>
                    <div class="alert alert-success"><?php echo $profile_pic_success; ?></div>
                <?php endif; ?>
                <?php if (!empty($profile_pic_error)): ?>
                    <div class="alert alert-danger"><?php echo $profile_pic_error; ?></div>
                <?php endif; ?>
                <form method="POST" id="editProfileForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="profile_pic" class="form-label">Choose an image</label>
                        <input type="file" class="form-control" name="profile_pic" accept=".jpg, .jpeg, .png" required>
                        <div id="profile-preview" class="mt-2"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="changeProfilePic" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('input[name="profile_pic"]').on('change', function(event) {
            const imagePreview = $('#profile-preview');
            imagePreview.empty(); // Clear previous previews

            $.each(event.target.files, function(index, file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = $('<img>', {
                        src: e.target.result,
                        css: {
                            width: '200px', // Set preview size
                            marginRight: '10px'
                        }
                    });
                    imagePreview.append(img);
                }
                reader.readAsDataURL(file);
            });
        });
    });
</script>