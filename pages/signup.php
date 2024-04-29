<?php
// Check session for email and password
if (isset($_SESSION['u_Email'])) {
    // Redirect to the feed page or any other appropriate page
    header("Location:home");
    exit(); // Stop further execution
}

// Condition for signup
if (isset($_POST['submit'])) {
    // Verify CAPTCHA response
    $captcha_response = $_POST['g-recaptcha-response'];
    $captcha_secret = '6LfDVMUpAAAAALRaMy-M7sEY0mPZGbj1fStxGhyl';
    $captcha_verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
        'secret' => $captcha_secret,
        'response' => $captcha_response
    );
    $options = array(
        'http' => array(
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context = stream_context_create($options);
    $captcha_verify_response = file_get_contents($captcha_verify_url, false, $context);
    $captcha_result = json_decode($captcha_verify_response);

    if (!$captcha_result->success) {
        // CAPTCHA verification failed, handle accordingly
        header("Location:&captcha-failed");
        exit();
    }

    // Sanitize first and last name
    $pattern_name = '/^[A-Za-z]+(?:-[A-Za-z]+)*$/';
    $firstname = mysqli_real_escape_string($con, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($con, $_POST['lastname']);

    $result_firstname = preg_match($pattern_name, $firstname);
    $result_lastname = preg_match($pattern_name, $lastname);

    // Validate email and confirm email
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $confirm_email = mysqli_real_escape_string($con, $_POST['confirm_email']);

    // Check if emails match
    if ($email == $confirm_email) {
        $validate_email = filter_var($email, FILTER_VALIDATE_EMAIL);
        $validate_confirm_email = filter_var($confirm_email, FILTER_VALIDATE_EMAIL);
    } else {
        header('location:&email-not-match');
        die();
    }

    // Sanitize password
    $pattern_pass = '/.{8,20}/';
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);

    // Check if passwords match
    if ($password == $confirm_password) {
        $result_password = preg_match($pattern_pass, $password);
        $result_confirm_password = preg_match($pattern_pass, $confirm_password);
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
    } else {
        header('location:&pw-not-match');
        die();
    }

    // Sanitize contact number
    $pattern_contact = '/09\d{9}/';
    $contact_num = mysqli_real_escape_string($con, $_POST['contact_num']);
    $result_contact = preg_match($pattern_contact, $contact_num);

    // Condition for sanitation and validation
    if ($result_firstname == 1 && $result_lastname == 1 && $validate_email && $validate_confirm_email && $result_password == 1 && $result_confirm_password == 1 && $result_contact == 1) {
        // Get account type and gender
        $account_type = mysqli_real_escape_string($con, $_POST['account_type']);
        $gender = mysqli_real_escape_string($con, $_POST['gender']);

        // Check if email already exists
        $check_query = "SELECT * FROM users WHERE u_Email = '$email'";
        $check_result = mysqli_query($con, $check_query);

        // If email doesn't exist, insert user data into the database
        if (mysqli_num_rows($check_result) == 0) {
            $insert_query = "INSERT INTO users (u_FName, u_LName, u_Email, u_Password, u_Contact_Number, u_Account_Type, u_Gender) 
                             VALUES ('$firstname', '$lastname', '$email', '$password_hash', '$contact_num', '$account_type', '$gender')";

            if (mysqli_query($con, $insert_query)) {
                header("location:login&register-success");
                exit(); // Stop further execution after redirect
            } else {
                echo "Error: " . $insert_query . "<br>" . mysqli_error($con);
                die();
            }
        } else {
            echo "Email already exists.";
            die();
        }
    }
}
?>

<section class="p-3 p-md-4 p-xl-5">
    <div class="container" style="padding-top:80px;">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-11">
                <div class="card border-light-subtle shadow-sm">
                    <div class="row g-0">
                        <div class="col-12 col-md-6">
                            <img class="img-fluid rounded-start w-100 h-100 object-fit-cover d-none d-md-block" loading="lazy" src="./img/yellow.jpg">
                        </div>
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
                            <div class="col-12 col-lg-11 col-xl-10">
                                <div class="card-body p-3 p-md-4 p-xl-5">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-5">
                                                <div class="text-center mb-4">
                                                    <a href="">
                                                        <img class="img-fluid rounded-start" src="./img/logo.png" width="auto" height="70">
                                                    </a>
                                                </div>
                                                <h2 class="h4 text-center">Registration</h2>
                                            </div>
                                        </div>
                                    </div>
                                    <form action="" method="post">
                                        <?php
                                        if (isset($_GET['captcha-failed'])) {
                                            echo '
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <strong>Recaptcha is required!</strong>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>';
                                        }
                                        if (isset($_GET['email-not-match'])) {
                                            echo '
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <strong>Email does not match!</strong>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>';
                                        }
                                        if (isset($_GET['pw-not-match'])) {
                                            echo '
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <strong>Password does not match!</strong>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>';
                                        }
                                        ?>
                                        <div class="row gy-3 overflow-hidden">
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="text" class="form-control" name="firstname" placeholder="First Name" required pattern="^[A-Za-z]+(?:-[A-Za-z]+)*$">
                                                    <label for="firstname" class="form-label">First Name</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="text" class="form-control" name="lastname" placeholder="First Name" required pattern="^[A-Za-z]+(?:-[A-Za-z]+)*$">
                                                    <label for="lastname" class="form-label">Last Name</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="email" class="form-control" name="email" placeholder="name@example.com" required>
                                                    <label for="email" class="form-label">Email</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="email" class="form-control" name="confirm_email" placeholder="name@example.com" required>
                                                    <label for="email" class="form-label">Confirm Email</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="password" class="form-control" name="password" placeholder="Password" minlength="8" maxlength="20" required pattern=".{8,20}">
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
                                                    <input class="form-check-input" type="radio" name="account_type" id="student" value="1" required>
                                                    <label class="form-check-label" for="student">Student</label>
                                                </div>
                                                <div class="form-check form-check-inline mb-3">
                                                    <input class="form-check-input" type="radio" name="account_type" id="Property Manager" value="0">
                                                    <label class="form-check-label" for="Property Manager">Property Manager</label>
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
                                                    <input class="form-check-input" type="checkbox" value="" name="iAgree" id="iAgree" required>
                                                    <label class="form-check-label text-secondary" for="iAgree">
                                                        I agree to the <a href="#!" class="link-primary text-decoration-none">terms and conditions</a>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 g-recaptcha d-grid" data-sitekey="6LfDVMUpAAAAAEkZN-4ynoTkLnFYkCRRdZuj3iSI" required></div>
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <button class="btn btn-dark btn-lg" name="submit" type="submit">Sign up</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="mb-0 mt-5 text-secondary text-center">Already have an account? <a href=".?page=login" class="link-primary text-decoration-none">Sign in</a></p>
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