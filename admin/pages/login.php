<?php

// Redirect to dashboard if already logged in
if (isset($_SESSION['a_ID'])) {
    header("Location: dashboard");
    exit(); // Stop further execution after redirect
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Sanitize and validate input
    $username = filter_var(trim($_POST['username']), FILTER_SANITIZE_STRING);
    $password = trim($_POST['password']); // No need to escape password here

    // Flag for errors
    $error_code = '';

    // Validate username (5-20 characters, alphanumeric or underscore)
    if (empty($username) || !preg_match('/^[A-Za-z0-9_]{5,20}$/', $username)) {
        $error_code = 'invalid-username-format';
    }

    // Validate password length (8-20 characters)
    if (empty($password) || !preg_match('/.{8,20}/', $password)) {
        $error_code = $error_code ?: 'invalid-password-length';
    }

    // Check for errors before querying the database
    if (!$error_code) {
        // Prepared statement to check if the username exists
        $query = "SELECT a_ID, a_Password, a_Username, a_ContactNumber FROM admin WHERE a_Username = ? LIMIT 1";
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        // Check if the username exists
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($adminId, $hashedPassword, $a_Username, $a_ContactNumber); // Fetch all necessary values
            $stmt->fetch();

            // Verify the password
            if (password_verify($password, $hashedPassword)) {
                // Set session variables and redirect to dashboard
                $_SESSION['a_ID'] = $adminId; // Correct variable name
                $_SESSION['a_Username'] = $a_Username; // Ensure this is set correctly
                $_SESSION['a_ContactNumber'] = $a_ContactNumber; // Ensure this is set correctly

                // Regenerate session ID for security
                session_regenerate_id(true);

                // Redirect to dashboard
                header("Location: dashboard");
                exit();
            } else {
                // Incorrect password
                $error_code = 'incorrect-password';
            }
        } else {
            // Username not found
            $error_code = 'username-not-found';
        }
    }

    // If there is an error, redirect back to login with error code
    if ($error_code) {
        header("Location: login.php?$error_code");
        exit();
    }

    // Close database connection
    $stmt->close();
    mysqli_close($con);
}
?>

<!-- HTML form for admin login -->
<section class="p-3 p-md-4 p-xl-5">
    <div class="container" style="padding-top: 80px;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xxl-5">
                <div class="card border-light-subtle shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <a href="">
                                <img class="img-fluid rounded-start" src="../assets/logo_img/logo-b.svg" width="auto" height="70" alt="Logo">
                            </a>
                            <p class="fw-bold">Administrator Login</p>
                        </div>

                        <!-- Display error messages based on URL error code -->
                        <?php
                        if (isset($_GET['invalid-username-format'])) {
                            echo '<div class="alert alert-danger">Invalid username format!</div>';
                        } elseif (isset($_GET['invalid-password-length'])) {
                            echo '<div class="alert alert-danger">Password must be between 8-20 characters!</div>';
                        } elseif (isset($_GET['username-not-found'])) {
                            echo '<div class="alert alert-danger">Username not found!</div>';
                        } elseif (isset($_GET['incorrect-password'])) {
                            echo '<div class="alert alert-danger">Incorrect password!</div>';
                        } elseif (isset($_GET['delete-success'])) {
                            echo "<div class='alert alert-success'>Account deleted successfully.</div>";
                        } elseif (isset($_GET['signup-success'])) {
                            echo "<div class='alert alert-success'>Account created successfully.</div>";
                        } elseif (isset($_GET['logout-success'])) {
                            echo "<div class='alert alert-success'>Logout Success!</div>";
                        }
                        ?>
                        <form method="post">
                            <div class="row gy-3">
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="username" id="username" autocomplete="on" placeholder="Username" required>
                                        <label for="username" class="form-label">Username</label>
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
                                <div class="d-flex gap-2 gap-md-4 flex-column flex-md-row justify-content-md-center mt-4">
                                    <a href="signup" class="link-secondary text-decoration-none">Create new account</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>