<?php
include('./config.php');

if (isset($_SESSION['email']) && isset($_SESSION['password'])) {
    // Redirect to the feed page or any other appropriate page
    header("Location: index.php?page=feed");
    exit(); // Stop further execution
}

// condition for logging in
if (isset($_POST['submit'])) {
    // Verify CAPTCHA response
    $captcha_response = $_POST['g-recaptcha-response'];
    $captcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $captcha_secret = '6LfDVMUpAAAAALRaMy-M7sEY0mPZGbj1fStxGhyl'; // Replace with your reCAPTCHA secret key
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
    $verify = file_get_contents($captcha_url, false, $context);
    $captcha_success = json_decode($verify);

    if (!$captcha_success->success) {
        // CAPTCHA verification failed, handle accordingly
        header("Location:?page=login&captcha-failed");
        exit(); // Stop further execution
    }
    // email validation
    $email = $_POST['email'];
    $validate_email = filter_var($email, FILTER_VALIDATE_EMAIL);
    // password sanitation
    $pattern_pass = '/.{8,20}/';
    $password = $_POST['password'];
    $result_password = preg_match($pattern_pass, $password);
    // sanitation and validation condition
    if ($validate_email && $result_password == 1) {
        // check email if it exists
        $checkEmail = mysqli_query($con, "SELECT * FROM users WHERE email = '$email' LIMIT 1");
        $countEmail = mysqli_num_rows($checkEmail);
        if ($countEmail == 1) {
            while ($row = mysqli_fetch_assoc($checkEmail)) {
                // fetch password and other data
                $dbPassword = $row['password'];
                $dbFirstname = $row['firstname'];
                $dbLastname = $row['lastname'];
                $dbAccounttype = $row['account_type'];
            }
            // de-hash hashed password
            $verifyPassword = password_verify($password, $dbPassword);
            if ($verifyPassword == 1) {
                // store data into session if credentials are correct
                $_SESSION['email'] = $email;
                $_SESSION['firstname'] = $dbFirstname;
                $_SESSION['lastname'] = $dbLastname;
                $_SESSION['account_type'] = $dbAccounttype;
                // redirect
                header("Location:?page=index");
                die();
            } else {
                // wrong password, back to login page
                header("location:?page=login&not-match-password");
                die();
            }
        } else {
            // wrong email redirect, back to login page
            header("location:?page=login&not-match-email");
            die();
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
                            <img class="img-fluid rounded-start w-100 h-100 object-fit-cover d-none d-md-block" loading="lazy" src="./img/stock.jpg"">
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
                                        if (isset($_GET['not-match-password'])) {
                                            echo '
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <strong>Wrong Password!</strong>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>';
                                        }
                                        if (isset($_GET['not-match-email'])) {
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
                                            <div class="col-12 g-recaptcha d-grid" data-sitekey="6LfDVMUpAAAAAEkZN-4ynoTkLnFYkCRRdZuj3iSI" required></div>
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
                                                <a href="index.php?page=signup" class="link-secondary text-decoration-none">Create new account</a>
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