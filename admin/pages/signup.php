<?php
// Redirect to dashboard if already logged in
if (isset($_SESSION['a_ID'])) {
    header("Location: dashboard");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Sanitize and validate input
    $username = trim(mysqli_real_escape_string($con, $_POST['username']));
    $contactNumber = trim(mysqli_real_escape_string($con, $_POST['contact_number']));
    $password = $_POST['password'];

    // Flag for errors
    $error = '';

    // Validate username
    if (empty($username)) {
        $error = "Username is required!";
    } elseif (!preg_match('/^[A-Za-z0-9_]{5,20}$/', $username)) { // Adjust this regex as needed
        $error = "Username must be between 5-20 characters and contain only letters, numbers, and underscores!";
    }

    // Validate contact number
    if (empty($contactNumber)) {
        $error = "Contact number is required!";
    } elseif (!preg_match('/^09\d{9}$/', $contactNumber)) {
        $error = "Invalid contact number format!";
    }

    // Validate password
    if (empty($password)) {
        $error = "Password is required!";
    } elseif (strlen($password) < 8 || strlen($password) > 20) {
        $error = "Password must be between 8-20 characters!";
    }

    // If there are any errors, display them
    if ($error) {
        // Store error message in session to display it later
        $_SESSION['error_message'] = $error;
    } else {
        try {
            // Check if the username already exists
            $checkQuery = "SELECT 1 FROM admin WHERE a_Username = ?";
            $checkStmt = $con->prepare($checkQuery);
            $checkStmt->bind_param("s", $username);
            $checkStmt->execute();
            $checkStmt->bind_result($count);
            $checkStmt->fetch();
            $checkStmt->close();

            if ($count > 0) {
                throw new Exception("Username already exists!");
            }

            // Hash the password before storing it
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Generate unique admin ID
            $adminId = uniqid('a_', true);

            // Insert admin into the database
            $query = "INSERT INTO admin (a_ID, a_Username, a_ContactNumber, a_Password) VALUES (?, ?, ?, ?)";
            $stmt = $con->prepare($query);
            $stmt->bind_param("ssss", $adminId, $username, $contactNumber, $hashedPassword);

            if ($stmt->execute()) {
                // Redirect to login page or dashboard
                header("Location: login?signup-success");
                exit();
            } else {
                throw new Exception("Error creating account: " . $stmt->error);
            }

            $stmt->close();
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage(); // Store exception message in session
        }
    }
}

// At the beginning of the page, check for errors and display them
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['error_message']); // Clear the error message after displaying it
?>

<!-- HTML form for signup -->
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
                            <p class="fw-bold">Administrator Signup</p>
                        </div>

                        <!-- Display error messages -->
                        <?php if (!empty($errorMessage)) echo '<div class="alert alert-danger">' . $errorMessage . '</div>'; ?>
                        <?php if (isset($_GET['signup_success'])) echo '<div class="alert alert-success">Signup successful! Please log in.</div>'; ?>

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
                                        <input type="text" class="form-control" name="contact_number" id="contact_number" autocomplete="on" placeholder="Contact Number" required>
                                        <label for="contact_number" class="form-label">Contact Number</label>
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
                                        <button class="btn btn-dark btn-lg" name="submit" type="submit">Sign up now</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2 gap-md-4 flex-column flex-md-row justify-content-md-center mt-4">
                                    <a href="login" class="link-secondary text-decoration-none">Already have an account? Log in</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>