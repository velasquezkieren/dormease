<?php
// Check if the user is already logged in, and redirect if true
if (isset($_SESSION['u_Email'])) {
    header("Location: profile");
    exit(); // Stop further execution after redirect
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Sanitize and validate input
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']); // No need to escape password here

    // Flag for errors
    $error_code = '';

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_code = 'invalid-email-format';
    }

    // Validate password length (8-20 characters)
    $pattern_pass = '/.{8,20}/';
    if (!preg_match($pattern_pass, $password)) {
        $error_code = $error_code ?: 'invalid-password-length';
    }

    // Check for errors before querying the database
    if (!$error_code) {
        // Prepared statement to check if the email exists
        $check_query = "SELECT u_ID, u_FName, u_MName, u_LName, u_Password, u_ContactNumber, u_Account_Type, u_Gender FROM user WHERE u_Email = ? LIMIT 1";
        $stmt = mysqli_prepare($con, $check_query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        // If email is found
        if (mysqli_stmt_num_rows($stmt) === 1) {
            mysqli_stmt_bind_result($stmt, $dbUserID, $dbFirstname, $dbMiddlename, $dbLastname, $dbPassword, $dbContactNo, $dbAccounttype, $dbGender);
            mysqli_stmt_fetch($stmt);

            // Verify the password hash
            if (password_verify($password, $dbPassword)) {
                // Password correct, set session variables
                $_SESSION['u_Email'] = $email;
                $_SESSION['u_ID'] = $dbUserID;
                $_SESSION['u_FName'] = $dbFirstname;
                $_SESSION['u_MName'] = $dbMiddlename;
                $_SESSION['u_LName'] = $dbLastname;
                $_SESSION['u_ContactNumber'] = $dbContactNo;
                $_SESSION['u_Account_Type'] = $dbAccounttype;
                $_SESSION['u_Gender'] = $dbGender;

                // Regenerate session ID for security
                session_regenerate_id(true);

                // Redirect to profile
                header("Location: profile?u_ID=" . $dbUserID);
                exit();
            } else {
                // Incorrect password
                $error_code = 'incorrect-password';
            }
        } else {
            // Email not found
            $error_code = 'email-not-found';
        }
    }

    // If there is an error, redirect back to login with error code
    if ($error_code) {
        header("Location: login&$error_code");
        exit();
    }

    // Close database connection
    mysqli_stmt_close($stmt);
    mysqli_close($con);
}
?>

<!-- HTML form for login -->
<section class="p-3 p-md-4 p-xl-5 min-vh-100">
    <div class="container" style="padding-top: 80px;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4"> <!-- Adjusted column size for responsiveness -->
                <!-- Right Column for Login Card -->
                <div class="card border-light-subtle shadow-sm">
                    <div class="card-body p-3 p-md-4 p-xl-5">
                        <h2 class="fw-bold text-center mb-3">Login</h2>
                        <form method="post">
                            <!-- Display error messages based on URL error code -->
                            <?php
                            if (isset($_GET['invalid-email-format'])) {
                                echo '<div class="alert alert-danger">Invalid email format!</div>';
                            } elseif (isset($_GET['invalid-password-length'])) {
                                echo '<div class="alert alert-danger">Password must be between 8-20 characters!</div>';
                            } elseif (isset($_GET['email-not-found'])) {
                                echo '<div class="alert alert-danger">Email not found!</div>';
                            } elseif (isset($_GET['incorrect-password'])) {
                                echo '<div class="alert alert-danger">Incorrect password!</div>';
                            } elseif (isset($_GET['auth-required'])) {
                                echo '<div class="alert alert-warning">Authentication Required!</div>';
                            } elseif (isset($_GET['logout-success'])) {
                                echo '<div class="alert alert-success">Logout Success!</div>';
                            } elseif (isset($_GET['signup-success'])) {
                                echo '<div class="alert alert-success">Account created successfully!</div>';
                            }
                            ?>

                            <div class="row gy-3 overflow-hidden">
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" name="email" id="email" autocomplete="on" placeholder="name@example.com" required>
                                        <label for="email" class="form-label">Email</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control" name="password" id="password" autocomplete="on" placeholder="Password" minlength="8" maxlength="20" required pattern=".{8,20}">
                                        <label for="password" class="form-label">Password</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button class="btn btn-dark btn-lg" name="submit" type="submit">Log in now</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-12">
                                <p class="mb-0 mt-5 text-secondary text-center">Don't have an account? <a href="signup" class="link-dark ">Create new account</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>