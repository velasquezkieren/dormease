<?php

// Check if the user is logged in
if (!isset($_SESSION['u_Email'])) {
    header("Location: login");
    exit();
}

// Get the u_ID from the URL
if (isset($_GET['u_ID'])) {
    $user_id = $_GET['u_ID'];

    // Fetch user data to prefill the form
    $query = "SELECT u_FName, u_MName, u_LName, u_Email, u_ContactNumber, u_Gender FROM user WHERE u_ID = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 's', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($result);

    // Check if user exists
    if (!$user_data) {
        echo "<script>alert('User not found.'); window.location.href='profile';</script>";
        exit();
    }
} else {
    echo "<script>alert('No user ID provided.'); window.location.href='profile';</script>";
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Sanitize and validate input
    $firstname = ucwords(trim(mysqli_real_escape_string($con, $_POST['firstname'])));
    $middlename = ucwords(trim(mysqli_real_escape_string($con, $_POST['mname'])));
    $lastname = ucwords(trim(mysqli_real_escape_string($con, $_POST['lastname'])));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $contact_num = trim(mysqli_real_escape_string($con, $_POST['contact_num']));
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $password = $_POST['password'] ?? ''; // Capture new password if provided
    $confirm_password = $_POST['confirm_password'] ?? '';
    $error_code = '';

    // Validate names
    $pattern_name = '/^[\p{L}\'\s\-]+$/u';
    if (
        strlen($firstname) < 2 || !preg_match($pattern_name, $firstname) ||
        (strlen($middlename) > 0 && !preg_match($pattern_name, $middlename)) ||
        strlen($lastname) < 2 || !preg_match($pattern_name, $lastname)
    ) {
        $error_code = 'invalid-name';
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_code = 'invalid-email-format';
    }

    // Validate contact number
    $pattern_contact = '/09\d{9}/';
    if (!preg_match($pattern_contact, $contact_num)) {
        $error_code = $error_code ?: 'invalid-contact';
    }

    // Validate password if provided
    if ($password !== '' && (strlen($password) < 8 || $password !== $confirm_password)) {
        $error_code = $error_code ?: 'passwords-mismatch';
    }

    // Check if email already exists (excluding the current user)
    $check_query = "SELECT 1 FROM user WHERE u_Email = ? AND u_ID != ?";
    $stmt = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($stmt, 'ss', $email, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error_code = $error_code ?: 'email-exists';
    }

    // If there is any error, redirect to edit-user with the error code
    if ($error_code) {
        header("Location: edit-user.php?u_ID=$user_id&error_code=$error_code");
        exit();
    }

    // Prepare user update query
    $update_query = "UPDATE user SET u_FName = ?, u_MName = ?, u_LName = ?, u_Email = ?, u_ContactNumber = ?, u_Gender = ?" . ($password ? ", u_Password = ?" : "") . " WHERE u_ID = ?";
    $stmt = mysqli_prepare($con, $update_query);

    // Bind parameters conditionally based on whether password is being updated
    if ($password) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        mysqli_stmt_bind_param($stmt, 'ssssssss', $firstname, $middlename, $lastname, $email, $contact_num, $gender, $password_hash, $user_id);
    } else {
        mysqli_stmt_bind_param($stmt, 'sssssss', $firstname, $middlename, $lastname, $email, $contact_num, $gender, $user_id);
    }

    if (mysqli_stmt_execute($stmt)) {
        header("Location: profile.php?update-success");
        exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
        exit();
    }
}
?>

<!-- HTML Section -->
<section class="p-3 p-md-4 p-xl-5 min-vh-100">
    <div class="container" style="padding-top:80px;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card border-light-subtle shadow-sm">
                    <div class="card-body p-3 p-md-4 p-xl-5">
                        <h2 class="fw-bold text-center mb-3">Edit Your Profile</h2>
                        <form action="" method="post">
                            <?php
                            $alerts = [
                                'email-exists' => 'Email already exists!',
                                'invalid-name' => 'Invalid name format!',
                                'invalid-email-format' => 'Invalid email format!',
                                'invalid-contact' => 'Invalid contact number!',
                                'passwords-mismatch' => 'Passwords do not match!'
                            ];

                            // Display error alerts
                            if (isset($_GET['error_code']) && array_key_exists($_GET['error_code'], $alerts)) {
                                $alertType = ($_GET['error_code'] === 'email-exists') ? 'warning' : 'danger';
                                echo "
                                <div class=\"alert alert-$alertType alert-dismissible fade show\" role=\"alert\">
                                    <strong>{$alerts[$_GET['error_code']]}</strong>
                                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                                </div>";
                            }
                            ?>
                            <div class="row gy-3 overflow-hidden">
                                <!-- First Name and Last Name side by side -->
                                <div class="col-12 col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="firstname" value="<?php echo htmlspecialchars($user_data['u_FName']); ?>" placeholder="First Name" required pattern="^[\p{L}'\s\-]+$" minlength="2">
                                        <label for="firstname" class="form-label">First Name</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="lastname" value="<?php echo htmlspecialchars($user_data['u_LName']); ?>" placeholder="Last Name" required pattern="^[\p{L}'\s\-]+$" minlength="2">
                                        <label for="lastname" class="form-label">Last Name</label>
                                    </div>
                                </div>

                                <!-- Middle Name and Contact Number side by side -->
                                <div class="col-12 col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="mname" value="<?php echo htmlspecialchars($user_data['u_MName']); ?>" placeholder="Middle Name" pattern="^[\p{L}'\s\-]+$" minlength="0"> <!-- Middle name is optional -->
                                        <label for="mname" class="form-label">Middle Name</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="contact_num" value="<?php echo htmlspecialchars($user_data['u_ContactNumber']); ?>" placeholder="Contact Number" required pattern="09\d{9}">
                                        <label for="contact_num" class="form-label">Contact Number</label>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($user_data['u_Email']); ?>" placeholder="name@example.com" required>
                                        <label for="email" class="form-label">Email</label>
                                    </div>
                                </div>

                                <!-- Gender selection -->
                                <div class="col-12">
                                    <div class="form-check form-check-inline mb-3">
                                        <input class="form-check-input" type="radio" name="gender" id="male" value="1" <?php echo $user_data['u_Gender'] == '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="male">Male</label>
                                    </div>
                                    <div class="form-check form-check-inline mb-3">
                                        <input class="form-check-input" type="radio" name="gender" id="female" value="0" <?php echo $user_data['u_Gender'] == '0' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="female">Female</label>
                                    </div>
                                </div>

                                <!-- Password and Confirm Password -->
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control" name="password" placeholder="New Password">
                                        <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm New Password">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    </div>
                                </div>

                                <!-- Submit button -->
                                <div class="col-12">
                                    <button type="submit" name="submit" class="btn btn-dark w-100">Update</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>