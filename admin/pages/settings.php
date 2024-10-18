<?php
// Initialize user details if logged in
if (isset($_SESSION['a_ID'])) {
    $a_ID = $_SESSION['a_ID']; // Store the logged-in user's ID for comparison
    $sql = "SELECT * FROM admin WHERE a_ID = ?";
    // Prepare and execute the query
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $a_ID);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    // Check if the user exists
    if (!$data) {
        $alert_message = "<div class='alert alert-danger'>User not found</div>";
        exit();
    }

    $a_Username = htmlspecialchars($data['a_Username']);
    $a_Contact = htmlspecialchars($data['a_ContactNumber']);
}

$alert_message = ''; // Initialize alert message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $error_code = '';
    $new_username = trim(mysqli_real_escape_string($con, $_POST['a_username']));
    $new_contact_num = trim(mysqli_real_escape_string($con, $_POST['contact_num']));
    $new_password = !empty(trim($_POST['password'])) ? $_POST['password'] : '';
    $confirm_password = !empty(trim($_POST['confirm_password'])) ? $_POST['confirm_password'] : '';

    // Validate password if provided
    if (!empty($new_password) && ($new_password !== $confirm_password || !preg_match('/.{8,20}/', $new_password))) {
        $error_code = $error_code ?: 'pw-not-match';
        $alert_message = "<div class='alert alert-danger'>Passwords do not match or invalid length.</div>";
    }

    // Validate contact number
    $pattern_contact = '/09\d{9}/'; // Assuming a contact number pattern for Philippine numbers (09xxxxxxxxx)
    if (!preg_match($pattern_contact, $new_contact_num)) {
        $error_code = $error_code ?: 'invalid-contact';
        $alert_message = "<div class='alert alert-danger'>Invalid contact number.</div>";
    }

    // Check if username already exists
    if ($new_username !== $a_Username) {
        $check_query = "SELECT 1 FROM admin WHERE a_Username = ? AND a_ID != ?";
        $stmt = $con->prepare($check_query);
        $stmt->bind_param('ss', $new_username, $a_ID);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_code = $error_code ?: 'username-exists';
            $alert_message = "<div class='alert alert-danger'>Username already exists.</div>";
        }
    }

    // If there is any error, set the alert message
    if ($error_code) {
        // No need to redirect, just show the alert
    } else {
        // Build the update query dynamically
        $update_fields = [];
        $params = [];
        $param_types = '';

        if ($new_username !== $a_Username) {
            $update_fields[] = 'a_Username = ?';
            $params[] = $new_username;
            $param_types .= 's';
        }
        if (!empty($new_password)) {
            $update_fields[] = 'a_Password = ?';
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            $param_types .= 's';
        }
        if ($new_contact_num !== $a_Contact) {
            $update_fields[] = 'a_ContactNumber = ?';
            $params[] = $new_contact_num;
            $param_types .= 's';
        }
        if (empty($update_fields)) {
            $alert_message = "<div class='alert alert-warning'>No changes made.</div>";
        } else {
            $update_query = "UPDATE admin SET " . implode(', ', $update_fields) . " WHERE a_ID = ?";
            $stmt = $con->prepare($update_query);
            $params[] = $a_ID;
            $param_types .= 's';
            $stmt->bind_param($param_types, ...$params);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                // Update session variables with new details
                $_SESSION['a_Username'] = $new_username;
                $_SESSION['a_ContactNumber'] = $new_contact_num;
                $alert_message = "<div class='alert alert-success'>Profile updated successfully.</div>";
            } else {
                $alert_message = "<div class='alert alert-danger'>No changes made or update failed.</div>";
            }
        }
    }
}

// Handle account deletion
if (isset($_POST['delete_account'])) {
    $delete_sql = "DELETE FROM admin WHERE a_ID = ?";
    $stmt = $con->prepare($delete_sql);
    $stmt->bind_param("s", $a_ID);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Account deleted, destroy session
        session_destroy();

        header("location:login?delete-success"); // Redirect to login or homepage
        exit();
    } else {
        $alert_message = "<div class='alert alert-danger'>Error deleting account.</div>";
    }
}
?>

<div class="container pt-5 mt-5">
    <div class="row pt-3">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-3 card-title">Account Settings</h3>
                    <?php if (!empty($alert_message)) echo $alert_message; ?> <!-- Display alert messages -->
                    <form id="editProfileForm" method="POST" action="">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="a_username" placeholder="Username" value="<?php echo $a_Username; ?>" required pattern="^[A-Za-zÀ-ÿ\s\'\-]+">
                            <label for="a_username" class="form-label">Username</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="contact_num" placeholder="Contact Number" value="<?php echo $a_Contact; ?>" required pattern="\d+">
                            <label for="contact_num" class="form-label">Contact Number</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" name="password" placeholder="Password" id="password" minlength="8" maxlength="20" pattern=".{8,20}">
                            <label for="password" class="form-label">Password (optional)</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm Password" minlength="8" maxlength="20" pattern=".{8,20}">
                            <label for="confirm_password" class="form-label">Confirm Password (optional)</label>
                        </div>
                        <button type="submit" class="btn btn-dark" name="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                                <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z" />
                            </svg>
                            Update Profile
                        </button>

                        <!-- Delete Account Button (trigger the modal) -->
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5" />
                            </svg>
                            Delete Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal for confirming account deletion -->
<div class="modal" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountModalLabel">Confirm Account Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete your account? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="">
                    <button type="submit" name="delete_account" class="btn btn-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                            <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5" />
                        </svg>
                        Delete Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>