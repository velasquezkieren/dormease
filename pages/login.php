<?php
// Check if the user is already logged in, and redirect if true
if (isset($_SESSION['u_Email'])) {
    header("Location: profile");
    exit(); // Stop further execution after redirect
}

// Condition for logging in
if (isset($_POST['submit'])) {

    // Email validation
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $validate_email = filter_var($email, FILTER_VALIDATE_EMAIL);

    // Password sanitation
    $pattern_pass = '/.{8,20}/';
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $result_password = preg_match($pattern_pass, $password);

    // Sanitation and validation condition
    if ($validate_email && $result_password == 1) {
        // Check if email exists
        $checkEmail = mysqli_query($con, "SELECT * FROM user WHERE u_Email = '$email' LIMIT 1");
        $countEmail = mysqli_num_rows($checkEmail);
        if ($countEmail == 1) {
            while ($row = mysqli_fetch_assoc($checkEmail)) {
                // Fetch password and other data
                $dbUserID = $row['u_ID'];
                $dbFirstname = $row['u_FName'];
                $dbLastname = $row['u_LName'];
                $dbGender = $row['u_Gender'];
                $dbPassword = $row['u_Password'];
                $dbAccounttype = $row['u_Account_Type'];
                $dbContactNo = $row['u_ContactNumber'];
            }
            // Verify hashed password
            if (password_verify($password, $dbPassword)) {
                // Store data into session if credentials are correct
                $_SESSION['u_Email'] = $email;
                $_SESSION['u_ID'] = $dbUserID;
                $_SESSION['u_FName'] = $dbFirstname;
                $_SESSION['u_LName'] = $dbLastname;
                $_SESSION['u_Gender'] = $dbGender;
                $_SESSION['u_ContactNumber'] = $dbContactNo;
                $_SESSION['u_Account_Type'] = $dbAccounttype;
                // Redirect
                header("Location: profile?u_ID=" . $dbUserID);
                exit();
            } else {
                // Wrong password, redirect back to login page
                header("Location:login&pw-not-match");
                exit();
            }
        } else {
            // Wrong email, redirect back to login page
            header("Location:login&email-not-match");
            exit(); // Stop further execution after redirect
        }
    }
}
?>

<section class="p-3 p-md-4 p-xl-5">
    <div class="container" style="padding-top: 80px;">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-11">
                <div class="card border-light-subtle shadow-sm">
                    <div class="row g-0">
                        <div class="col-12 col-md-6">
                            <img class="img-fluid rounded-start w-100 h-100 object-fit-cover d-none d-md-block" loading="lazy" src="./img/yellow.jpg"">
                        </div>
                        <div class=" col-12 col-md-6 d-flex align-items-center justify-content-center">
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
                                            </div>
                                        </div>
                                    </div>
                                    <form action="" method="post">
                                        <?php
                                        // alert box, Oniel if kaya mo mag-JQuery, para maganda user exp hehe pa-replace neto -Kieren
                                        if (isset($_GET['logout-success'])) {
                                            echo '
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <strong>Logout Success</strong>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                            ';
                                        }
                                        if (isset($_GET['register-success'])) {
                                            echo '
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <strong>Registration Success</strong>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                            ';
                                        }
                                        if (isset($_GET['auth-required'])) {
                                            echo '
                                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                                <strong>Authorization is required!</strong>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>';
                                        }
                                        if (isset($_GET['pw-not-match'])) {
                                            echo '
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <strong>Wrong Password!</strong>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>';
                                        }
                                        if (isset($_GET['email-not-match'])) {
                                            echo '
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <strong>Wrong Email!</strong>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>';
                                        }
                                        if (isset($_GET['captcha-failed'])) {
                                            echo '
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <strong>Recaptcha is required!</strong>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>';
                                        }
                                        ?>
                                        <div class="row gy-3 overflow-hidden">
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="email" class="form-control" name="email" id="email" placeholder="name@example.com" required>
                                                    <label for="email" class="form-label">Email</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="password" class="form-control" name="password" id="password" value="" placeholder="Password" minlength="8" maxlength="20" required pattern=".{8,20}">
                                                    <label for="password" class="form-label">Password</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="" name="remember_me" id="remember_me">
                                                    <label class="form-check-label text-secondary" for="remember_me">
                                                        Keep me logged in
                                                    </label>
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
                                            <div class="d-flex gap-2 gap-md-4 flex-column flex-md-row justify-content-md-center mt-5">
                                                <a href="signup" class="link-secondary text-decoration-none">Create new account</a>
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
    </div>
</section>