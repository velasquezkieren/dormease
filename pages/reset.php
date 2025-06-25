<?php
require 'vendor/autoload.php'; // Ensure the path is correct to where 'vendor' is located

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the reset token is provided in the URL
if (isset($_GET['token'])) {
    $reset_token = $_GET['token'];

    // Prepare query to check if the reset token exists in the database and is not expired
    $query = "SELECT u_ID, expires_at FROM password_resets WHERE reset_token = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 's', $reset_token);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $userID, $expires_at);
        mysqli_stmt_fetch($stmt);

        // Check if the token has expired
        if (strtotime($expires_at) > time()) {
            // Token is valid and not expired, display password reset form
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
                $new_password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];

                // Validate passwords
                if ($new_password === $confirm_password) {
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update the password in the database
                    $update_query = "UPDATE user SET u_Password = ? WHERE u_ID = ?";
                    $update_stmt = mysqli_prepare($con, $update_query);
                    mysqli_stmt_bind_param($update_stmt, 'ss', $hashed_password, $userID);
                    if (mysqli_stmt_execute($update_stmt)) {
                        // Delete the reset token from the database to prevent reuse
                        $delete_query = "DELETE FROM password_resets WHERE reset_token = ?";
                        $delete_stmt = mysqli_prepare($con, $delete_query);
                        mysqli_stmt_bind_param($delete_stmt, 's', $reset_token);
                        mysqli_stmt_execute($delete_stmt);

                        // Redirect with success message
                        header("Location: login?password-reset-success");
                        exit();
                    } else {
                        // Database error
                        echo '<div class="alert alert-danger">Failed to reset password. Please try again later.</div>';
                    }
                } else {
                    // Passwords do not match
                    echo '<div class="alert alert-danger">Passwords do not match.</div>';
                }
            }
        } else {
            // Token expired
            echo '<div class="alert alert-danger">The reset link has expired. Please request a new one.</div>';
        }
    } else {
        // Token not found
        echo '<div class="alert alert-danger">Invalid or expired reset token.</div>';
    }
} else {
    // No token provided
    echo '<div class="alert alert-danger">Invalid reset link.</div>';
}
?>

<!-- HTML form for resetting the password -->
<section class="p-3 p-md-4 p-xl-5 min-vh-100">
    <div class="container" style="padding-top: 80px;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4"> <!-- Adjusted column size for responsiveness -->
                <!-- Right Column for Reset Password Card -->
                <div class="card border-light-subtle shadow-sm">
                    <div class="card-body p-3 p-md-4 p-xl-5">
                        <h2 class="fw-bold text-center mb-3">Reset Password</h2>
                        <form method="post">
                            <div class="row gy-3 overflow-hidden">
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control" name="password" id="password" required>
                                        <label for="password" class="form-label">New Password</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button class="btn btn-dark btn-lg" name="submit" type="submit">Reset Password</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-12">
                                <p class="mb-0 mt-5 text-secondary text-center">Remembered your password? <a href="login" class="link-dark ">Log in</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>