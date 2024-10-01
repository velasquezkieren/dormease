<?php
// Check session for email and password
if (isset($_SESSION['u_Email'])) {
    // Redirect to the feed page or any other appropriate page
    header("Location:home");
    exit(); // Stop further execution
}

// Process signup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    // Sanitize and validate input
    $firstname = ucwords(trim(mysqli_real_escape_string($con, $_POST['firstname'])));
    $lastname = ucwords(trim(mysqli_real_escape_string($con, $_POST['lastname'])));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $confirm_email = filter_var(trim($_POST['confirm_email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // No need to escape this
    $confirm_password = $_POST['confirm_password']; // No need to escape this
    $contact_num = trim(mysqli_real_escape_string($con, $_POST['contact_num']));
    $account_type = mysqli_real_escape_string($con, $_POST['account_type']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);

    // Extract the local part of the email (before the @ symbol)
    $email_parts = explode('@', $email);
    $local_part = $email_parts[0] ?? '';

    // Flag for errors
    $error_code = '';

    // Validate names
    $pattern_name = '/^[A-Za-zÀ-ÿ\s\'\-]+$/u'; // Minimum 2 characters
    if (
        !preg_match($pattern_name, $firstname) || !preg_match($pattern_name, $lastname)
    ) {
        $error_code = 'invalid-name';
    }

    // Validate email
    if ($email !== $confirm_email) {
        $error_code = 'email-not-match';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_code = 'invalid-email-format';
    } elseif (strlen($local_part) < 6) {
        $error_code = 'email-local-part-short';
    }

    // Validate password
    $pattern_pass = '/.{8,20}/';
    if ($password !== $confirm_password || !preg_match($pattern_pass, $password)) {
        $error_code = $error_code ?: 'pw-not-match';
    }

    // Validate contact number
    $pattern_contact = '/09\d{9}/';
    if (!preg_match($pattern_contact, $contact_num)) {
        $error_code = $error_code ?: 'invalid-contact';
    }

    // Check if email already exists
    $check_query = "SELECT 1 FROM user WHERE u_Email = ?";
    $stmt = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error_code = $error_code ?: 'email-exists';
    }

    // If there is any error, redirect to signup with the error code
    if ($error_code) {
        header("Location: signup&$error_code");
        exit();
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user data into database
    $u_ID = uniqid('u_');
    $insert_query = "INSERT INTO user (u_ID, u_FName, u_LName, u_Email, u_Password, u_ContactNumber, u_Account_Type, u_Gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $insert_query);
    mysqli_stmt_bind_param($stmt, 'ssssssss', $u_ID, $firstname, $lastname, $email, $password_hash, $contact_num, $account_type, $gender);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: login&register-success");
        exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
        exit();
    }
}
?>

<!-- HTML Section -->
<section class="p-3 p-md-4 p-xl-5">
    <div class="container" style="padding-top:80px;">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-11">
                <div class="card border-light-subtle shadow-sm">
                    <div class="row g-0">
                        <div class="col-12 col-md-6">
                            <img class="img-fluid rounded-start w-100 h-100 object-fit-cover d-none d-md-block" loading="lazy" src="./img/yellow.jpg" alt="Background Image">
                        </div>
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
                            <div class="col-12 col-lg-11 col-xl-10">
                                <div class="card-body p-3 p-md-4 p-xl-5">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-5">
                                                <div class="text-center mb-4">
                                                    <a href="">
                                                        <img class="img-fluid rounded-start" src="./assets/logo_img/logo-b.svg" width="auto" height="70" alt="Logo">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <form action="" method="post">
                                        <?php
                                        $alerts = [
                                            'email-not-match' => 'Email does not match!',
                                            'email-local-part-short' => 'Email must be at least 6 characters long!',
                                            'invalid-email-format' => 'Invalid email format!',
                                            'pw-not-match' => 'Password does not match!',
                                            'email-exists' => 'Email already exists!',
                                            'invalid-name' => 'Invalid name format!',
                                            'invalid-contact' => 'Invalid contact number!'
                                        ];

                                        foreach ($alerts as $key => $message) {
                                            if (isset($_GET[$key])) {
                                                $alertType = ($key === 'email-exists' || $key === 'email-local-part-short') ? 'warning' : 'danger';
                                                echo "
                                                <div class=\"alert alert-$alertType alert-dismissible fade show\" role=\"alert\">
                                                    <strong>$message</strong>
                                                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                                                </div>";
                                            }
                                        }
                                        ?>
                                        <div class="row gy-3 overflow-hidden">
                                            <!-- Form fields with improved HTML attributes for better validation -->
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="text" class="form-control" name="firstname" placeholder="First Name" required pattern="^[A-Za-zÀ-ÿ\s\'\-]+">
                                                    <label for="firstname" class="form-label">First Name</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="text" class="form-control" name="lastname" placeholder="Last Name" required pattern="^[A-Za-zÀ-ÿ\s\'\-]+">
                                                    <label for="lastname" class="form-label">Last Name</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="email" class="form-control" name="email" id="email" placeholder="name@example.com" required>
                                                    <label for="email" class="form-label">Email</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="email" class="form-control" name="confirm_email" id="confirm_email" placeholder="name@example.com" required>
                                                    <label for="confirm_email" class="form-label">Confirm Email</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="password" class="form-control" name="password" placeholder="Password" id="password" minlength="8" maxlength="20" required pattern=".{8,20}">
                                                    <label for="password" class="form-label">Password</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm Password" minlength="8" maxlength="20" required pattern=".{8,20}">
                                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="text" class="form-control" name="contact_num" placeholder="Contact Number" required pattern="09\d{9}">
                                                    <label for="contact_num" class="form-label">Contact Number</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check form-check-inline mb-3">
                                                    <input class="form-check-input" type="radio" name="account_type" id="resident" value="1" required>
                                                    <label class="form-check-label" for="resident">Resident</label>
                                                </div>
                                                <div class="form-check form-check-inline mb-3">
                                                    <input class="form-check-input" type="radio" name="account_type" id="property_manager" value="0">
                                                    <label class="form-check-label" for="property_manager">Property Manager</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check form-check-inline mb-3">
                                                    <input class="form-check-input" type="radio" name="gender" id="male" value="1" required>
                                                    <label class="form-check-label" for="male">Male</label>
                                                </div>
                                                <div class="form-check form-check-inline mb-3">
                                                    <input class="form-check-input" type="radio" name="gender" id="female" value="0">
                                                    <label class="form-check-label" for="female">Female</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="iAgree" id="iAgree" required>
                                                    <label class="form-check-label text-secondary" for="iAgree">
                                                        I agree to the <a href="#!" class="link-primary text-decoration-none">terms and conditions</a>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <button class="btn btn-dark btn-lg" name="submit" type="submit">Sign up</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="mb-0 mt-5 text-secondary text-center">Already have an account? <a href="login" class="link-primary text-decoration-none">Sign in</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>