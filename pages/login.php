<?php
include('./config.php');

if (isset($_SESSION['email']) && isset($_SESSION['password'])) {
    // Redirect to the feed page or any other appropriate page
    header("Location: index.php?page=feed");
    exit(); // Stop further execution
}

// condition for logging in
if (isset($_POST['submit'])) {
    // email validation
    $email = $_POST['email'];
    $validate_email = filter_var($email, FILTER_VALIDATE_EMAIL);

    // password sanitation
    $pattern_pass = '/.{8,20}/';
    $password = $_POST['password'];
    $result_password = preg_match($pattern_pass, $password);

    // sanitation and validation condition
    if ($validate_email && $result_password == 1) {
        $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
        $query = mysqli_query($con, $sql);
        $row = mysqli_num_rows($query);
        $data = mysqli_fetch_assoc($query);
        if ($row < 1) {
            // wrong credentials redirect back to login page
            header("location:?page=login");
            die();
            exit();
        } else {
            // correct credentials
            $_SESSION['email'] = $email;
            $_SESSION['password'] = $password;
            $_SESSION['firstname'] = $data['firstname'];
            $_SESSION['lastname'] = $data['lastname'];
            $_SESSION['account_type'] = $data['account_type'];
            header("Location:?page=index");
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