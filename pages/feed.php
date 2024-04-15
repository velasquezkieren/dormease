<?php
include('./config.php');

if (!isset($_SESSION['email']) && (!isset($_SESSION['password']))) {
    header("location:?page=index");
    die();
}

$email = $_SESSION['email'];
$password = $_SESSION['password'];
$sql = "SELECT * from users WHERE email = '$email' and password='$password'";
$query = mysqli_query($con, $sql);
$data = mysqli_fetch_assoc($query);

$lastname = $data['lastname'];
$firstname = $data['firstname'];
$fullname =  ucwords($firstname) . " " . ucwords($lastname);
?>

<body>
    Hello <?php echo $fullname; ?>
</body>