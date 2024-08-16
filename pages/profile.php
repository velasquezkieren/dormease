<?php
include('./config.php');

if (!isset($_SESSION['u_Email'])) {
    header("location:login&auth-required");
    die();
}

// Check if u_ID is provided in the URL
if (isset($_GET['u_ID'])) {
    $user_ID = $_GET['u_ID'];
    // Fetch the user data based on u_ID
    $sql = "SELECT * FROM users WHERE u_ID = '$user_ID'";
} else {
    // If no u_ID is provided, fetch the logged-in user's profile
    $email = $_SESSION['u_Email'];
    $sql = "SELECT * FROM users WHERE u_Email = '$email'";
    $user_ID = $_SESSION['u_ID'];  // Store the logged-in user's ID for comparison
}

$query = mysqli_query($con, $sql);
$data = mysqli_fetch_assoc($query);

// Check if the user exists
if (!$data) {
    echo "User not found.";
    die();
}
$email = $_SESSION['u_Email'];
$contact_num = $_SESSION['u_Contact_Number'];
$lastname = $data['u_LName'];
$firstname = $data['u_FName'];

$fullname = ucwords($firstname) . " " . ucwords($lastname);

// Condition for edit profile
if (isset($_POST['submit'])) {
    // Sanitize first and last name
    $pattern_name = '/^[A-Za-z]+(?:-[A-Za-z]+)*$/';
    $firstname = mysqli_real_escape_string($con, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($con, $_POST['lastname']);

    $result_firstname = preg_match($pattern_name, $firstname);
    $result_lastname = preg_match($pattern_name, $lastname);

    // Validate email
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $validate_email = filter_var($email, FILTER_VALIDATE_EMAIL);

    // Sanitize password
    $pattern_pass = '/.{8,20}/';
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $result_password = preg_match($pattern_pass, $password);
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Sanitize contact number
    $pattern_contact = '/09\d{9}/';
    $contact_num = mysqli_real_escape_string($con, $_POST['contact_num']);
    $result_contact = preg_match($pattern_contact, $contact_num);

    if ($result_firstname == 1 && $result_lastname == 1 && $validate_email && $result_password == 1 && $result_confirm_password == 1 && $result_contact == 1) {
        $update_sql = "UPDATE users SET 
                        u_FName = '$firstname',
                        u_LName = '$lastname',
                        u_Email = '$email',
                        u_Password = '$password_hash',
                        u_Contact_Number = '$contact_num'
                        WHERE u_ID = '$user_ID'";
        if (mysqli_query($con, $update_sql)) {
            echo "Profile updated successfully.";
            // Refresh the page to reflect the changes
            header("Location: profile");
            die();
        } else {
            echo "Error updating profile: " . mysqli_error($con);
        }
    } else {
        echo "Invalid input. Please check your data.";
    }
}
?>

<div class="container pt-5">
    <div class="row pt-5">
        <div class="col-12 col-md-6 pt-5 d-flex justify-content-center justify-content-md-start">
            <?php
            // Check if the logged-in user is viewing their own profile
            if (isset($_GET['u_ID']) && $_GET['u_ID'] != $_SESSION['u_ID']) {
                echo '<p class="h1 text-center text-md-left">' . $fullname . '</p>'; // Show the full name for another user's profile
            } else {
                echo '<p class="h1 text-center text-md-left">Welcome, ' . $firstname . '!</p>'; // Show the greeting if it's their own profile
            }
            ?>
        </div>
        <div class="col-12 col-md-6 pt-3 pt-md-5 d-flex justify-content-center justify-content-md-end align-items-center">
            <?php
            // Check if the profile belongs to the logged-in user or is being viewed by someone else
            if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 0 && (!isset($_GET['u_ID']) || $_GET['u_ID'] == $_SESSION['u_ID'])) {
                // Display the "Create a listing" button if the account type is 0 and the user is viewing their own profile
                echo '<a class="login-button" href="list">Create a listing</a>';
            } elseif (!isset($_GET['u_ID']) || $_GET['u_ID'] == $_SESSION['u_ID']) {
                // Display the "Edit Profile" button if the user is viewing their own profile
                echo '<button type="button" class="btn login-button" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>';
                // echo '<a class="login-button" href="profile?edit_ID=' . $user_ID . '">Edit Profile</a>';
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

<!-- Edit Profile Modal -->
<div class="modal" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm" method="POST" action="">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="firstname" placeholder="First Name" value="<?php echo $firstname; ?>" required pattern="^[A-Za-z]+(?:-[A-Za-z]+)*$">
                        <label for="firstname" class="form-label">First Name</label>
                    </div>
                    <div class="mb-3">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="lastname" placeholder="First Name" value="<?php echo $lastname; ?>" required pattern="^[A-Za-z]+(?:-[A-Za-z]+)*$">
                            <label for="lastname" class="form-label">Last Name</label>
                        </div>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" name="email" placeholder="name@example.com" value="<?php echo $email; ?>" required>
                        <label for="email" class="form-label">Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" name="password" placeholder="Password" minlength="8" maxlength="20" required pattern=".{8,20}">
                        <label for="password" class="form-label">Password</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="contact_num" placeholder="Contact Number" value="<?php echo $contact_num; ?>" required pattern="09\d{9}">
                        <label for="contact_num" class="form-label">Contact Number</label>
                    </div>
                    <!-- Add more fields as needed -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-dark">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>