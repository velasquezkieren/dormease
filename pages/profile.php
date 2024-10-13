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

$profile_pic = !empty($data['u_Picture']) && file_exists('upload/' . htmlspecialchars($data['u_Picture']))
    ? 'upload/' . htmlspecialchars($data['u_Picture'])
    : 'user_avatar/default_avatar.png';

$email = htmlspecialchars($data['u_Email']);
$contact_num = htmlspecialchars($data['u_ContactNumber']);
$lastname = htmlspecialchars($data['u_LName']);
$firstname = htmlspecialchars($data['u_FName']);
$middlename = htmlspecialchars($data['u_MName']);
$account_type = htmlspecialchars($data['u_Account_Type']);
$fullname = ucwords($firstname) . " " . ucwords($middlename) . " " . ucwords($lastname);

// Handle form submission for profile edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $error_code = '';

    // Sanitize input
    $new_firstname = ucwords(trim(mysqli_real_escape_string($con, $_POST['firstname'])));
    $new_middlename = ucwords(trim(mysqli_real_escape_string($con, $_POST['middlename'])));
    $new_lastname = ucwords(trim(mysqli_real_escape_string($con, $_POST['lastname'])));
    $new_contact_num = trim(mysqli_real_escape_string($con, $_POST['contact_num']));
    $new_email = !empty(trim($_POST['email'])) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : $email;
    $new_password = !empty(trim($_POST['password'])) ? $_POST['password'] : '';
    $confirm_email = !empty(trim($_POST['confirm_email'])) ? filter_var(trim($_POST['confirm_email']), FILTER_SANITIZE_EMAIL) : $new_email;
    $confirm_password = !empty(trim($_POST['confirm_password'])) ? $_POST['confirm_password'] : '';

    // Validate names
    $pattern_name = '/^[A-Za-zÀ-ÿ\s\'\-]+$/u';
    if (!preg_match($pattern_name, $new_firstname) || !preg_match($pattern_name, $new_middlename) || !preg_match($pattern_name, $new_lastname)) {
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
        $params[] = $new_middlename;
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
            if (file_exists($file_path) && !unlink($file_path)) {
                throw new Exception("Failed to delete profile picture.");
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
                if (file_exists($file_path) && !unlink($file_path)) {
                    throw new Exception("Failed to delete dormitory image: " . $file_path);
                }
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

?>

<!-- HTML Section -->
<div class="container pt-5" style="margin-top: 100px;"> <!-- Adjust margin for fixed navbar -->
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
            <div class="d-flex flex-column align-items-center text-center">
                <a href="#">
                    <!-- profile picture here -->
                    <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#changeProfilePicModal">
                        <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="<?php echo htmlspecialchars($fullname); ?>" class="img-fluid mb-3 rounded-circle">
                    </button>
                </a>
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
                                    echo ($_SESSION['u_Account_Type'] == 0) ? 'Owner' : 'Tenant';
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
                                        echo ($row['u_Account_Type'] == 0) ? 'Owner' : 'Tenant';
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
                                        <a href="application?u_ID=<?php echo htmlspecialchars($user_ID); ?>">
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

<?php
include('profile-modal.php');
?>

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