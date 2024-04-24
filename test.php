<?php
// backup sa signup.php
if (isset($_POST['submit'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    // $confirm_password = $_POST['confirm_password'];
    $contact_num = $_POST['contact_num'];
    $account_type = $_POST['account_type'];
    $gender = $_POST['gender'];

    // Check if the email already exists
    $check_query = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($con, $check_query);

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
        }
    }
}
?>

<?php
// backup for login.php
if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $query = mysqli_query($con, $sql);
    $row = mysqli_num_rows($query);
    $data = mysqli_fetch_assoc($query);
    if ($row < 1) {
        header("location:?page=login");
        die();
        exit();
    } else {
        $_SESSION['email'] = $email;
        $_SESSION['password'] = $password;
        $_SESSION['firstname'] = $data['firstname'];
        $_SESSION['lastname'] = $data['lastname'];
        $_SESSION['account_type'] = $data['account_type'];
        header("Location:?page=index");
        die();
    }
}

?>


<?php
// login backup 4/24/2024
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
            header("location:?page=login&not-match");
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