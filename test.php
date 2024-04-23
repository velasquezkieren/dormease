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