<?php
include('./config.php');

// check session for email and pass
if (isset($_SESSION['email']) && isset($_SESSION['password'])) {
    // Redirect to the feed page or any other appropriate page
    header("Location: index.php?page=index");
    exit(); // Stop further execution
}
// condition sa signup  nahihilo na ko -kieren
if (isset($_POST['submit'])) {
    // sanitize first and last name
    $pattern_name = '/^[A-Za-z]+(?:-[A-Za-z]+)*$/';
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];

    $result_firstname = preg_match($pattern_name, $firstname);
    $result_lastname = preg_match($pattern_name, $lastname);

    // validate email and confirm email
    $email = $_POST['email'];
    $confirm_email = $_POST['confirm_email'];
    // check if match
    if ($email == $confirm_email) {
        $validate_email = filter_var($email, FILTER_VALIDATE_EMAIL);
        $validate_confirm_email = filter_var($confirm_email, FILTER_VALIDATE_EMAIL);
    } else {
        echo "Email doesn't match";
        die();
    }

    // sanitize password
    $pattern_pass = '/.{8,20}/';
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    // check if match
    if ($password == $confirm_password) {
        $result_password = preg_match($pattern_pass, $password);
        $result_confirm_password = preg_match($pattern_pass, $confirm_password);
    } else {
        echo "Password doesn't match";
        die();
    }

    // sanitize contact number
    $pattern_contact = '/09\d{9}/';
    $contact_num = $_POST['contact_num'];
    $result_contact = preg_match($pattern_contact, $contact_num);

    // condition for sanitation and validation
    if ($result_firstname == 1 && $result_lastname == 1 && $validate_email && $validate_confirm_email && $result_password == 1 && $result_confirm_password == 1 && $result_contact == 1) {
        // pasok yung data from radio buttons
        $account_type = $_POST['account_type'];
        $gender = $_POST['gender'];

        // check if existing email na yung nireregister ni user
        $check_query = "SELECT * FROM users WHERE email = '$email'";
        $check_result = mysqli_query($con, $check_query);

        // condition na para sa bwakananginang insert database
        if (mysqli_num_rows($check_result) > 0) {
            echo "Email already exists.";
        } else {
            // Insert user data into database
            $insert_query = "INSERT INTO users (firstname, lastname, email, password, contact_num, account_type, gender) VALUES ('$firstname', '$lastname', '$email', '$password', '$contact_num', '$account_type', '$gender')";

            if (mysqli_query($con, $insert_query)) {
                header("location:?page=login");
                // You can redirect the user to a login page or any other page after successful registration
            } else {
                echo "Error: " . $insert_query . "<br>" . mysqli_error($con);
                die();
            }
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
                            <img class="img-fluid rounded-start w-100 h-100 object-fit-cover d-none d-md-block" loading="lazy" src="./img/stock.jpg" alt="Welcome back you've been missed!">
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
                                                    <input type="tel" class="form-control" name="contact_num" placeholder="Contact Number" required pattern="09\d{9}">
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