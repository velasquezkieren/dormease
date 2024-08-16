<?php
include('./config.php');

if (!isset($_SESSION['u_Email'])) {
    header("location:login&auth-required");
    die();
}

// Check if u_ID is provided in the URL
if (isset($_GET['u_ID'])) {
    $user_ID = $_GET['u_ID'];
    $sql = "SELECT * FROM users WHERE u_ID = '$user_ID'";
} else {
    $email = $_SESSION['u_Email'];
    $sql = "SELECT * FROM users WHERE u_Email = '$email'";
    $user_ID = $_SESSION['u_ID'];  // Store the logged-in user's ID for comparison
}

$query = mysqli_query($con, $sql);
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "User not found.";
    die();
}

$lastname = $data['u_LName'];
$firstname = $data['u_FName'];
$email = $data['u_Email'];
$contact_num = $data['u_Contact_Number'];

$fullname = ucwords($firstname) . " " . ucwords($lastname);

if (isset($_POST['submit'])) {
    $pattern_name = '/^[A-Za-z]+(?:-[A-Za-z]+)*$/';
    $firstname = mysqli_real_escape_string($con, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($con, $_POST['lastname']);

    $result_firstname = preg_match($pattern_name, $firstname);
    $result_lastname = preg_match($pattern_name, $lastname);

    $email = mysqli_real_escape_string($con, $_POST['email']);
    $validate_email = filter_var($email, FILTER_VALIDATE_EMAIL);

    $password = mysqli_real_escape_string($con, $_POST['password']);
    $result_password = preg_match('/.{8,20}/', $password);

    $contact_num = mysqli_real_escape_string($con, $_POST['contact_num']);
    $result_contact = preg_match('/09\d{9}/', $contact_num);

    if ($result_firstname && $result_lastname && $validate_email && $result_password && $result_contact) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET u_FName='$firstname', u_LName='$lastname', u_Email='$email', u_Password='$password_hash', u_Contact_Number='$contact_num' WHERE u_ID='$user_ID'";
        if (mysqli_query($con, $sql)) {
            $_SESSION['u_Email'] = $email; // Update session email
            header("location:profile?u_ID=$user_ID");
        } else {
            echo "Error updating profile: " . mysqli_error($con);
        }
    }
}
