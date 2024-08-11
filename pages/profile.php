<?php
include('./config.php');
if (!isset($_SESSION['u_Email'])) {
    header("location:login&auth-required");
    die();
}
$email = $_SESSION['u_Email'];
$sql = "SELECT * from users WHERE u_Email = '$email'";
$query = mysqli_query($con, $sql);
$data = mysqli_fetch_assoc($query);

$lastname = $data['u_LName'];
$firstname = $data['u_FName'];
$fullname =  ucwords($firstname) . " " . ucwords($lastname);

?>

<div class="container pt-5">
    <div class="row pt-5">
        <div class="col-12 col-md-6 pt-5 d-flex justify-content-center justify-content-md-start">
            <p class="h1 text-center text-md-left">Welcome, <?php echo $firstname; ?>!</p>
        </div>
        <div class="col-12 col-md-6 pt-3 pt-md-5 d-flex justify-content-center justify-content-md-end align-items-center">
            <?php
            if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 0) {
                // Display the "Create a listing" button if the account type is 0
                echo '<a class="login-button" href="list">Create a listing</a>';
            } else {
                echo '<a class="login-button" href="profile">Edit Profile</a>';
            }
            ?>

        </div>
    </div>
    <div class="row">
        <div class="col pt-5">
            <div class="col-12 col-md-6 pt-5 d-flex justify-content-center justify-content-md-start">
                <?php
                if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 0) {
                    echo '<p class="h2 pb-5">My Listings</p>';
                } else {
                    echo '<p class="h2 pb-5">Statement of Account</p>';
                }
                ?>

            </div>
            <div class="alert alert-secondary text-center mx-auto p-5" role="alert">
                No listings available at the moment
            </div>
        </div>
    </div>
</div>