<?php
require 'vendor/autoload.php';  // Ensure the path is correct to where 'vendor' is located

// Use PHPMailer's classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the user is already logged in, and redirect if true
if (isset($_SESSION['u_Email'])) {
    header("Location: profile");
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Sanitize and validate email input
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Prepare query to check if email exists in the database
        $query = "SELECT u_ID, u_FName FROM user WHERE u_Email = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_bind_result($stmt, $dbUserID, $dbFirstName);
            mysqli_stmt_fetch($stmt);

            // Generate a unique token and store it in the password_resets table
            $reset_token = bin2hex(random_bytes(16)); // Generate a random token
            $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

            // Insert reset token into the database
            $insert_query = "INSERT INTO password_resets (reset_token, u_ID, expires_at) VALUES (?, ?, ?)";
            $insert_stmt = mysqli_prepare($con, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, 'sss', $reset_token, $dbUserID, $expires_at);
            mysqli_stmt_execute($insert_stmt);

            // Send reset link via email
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Gmail's SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'dormease2024@gmail.com'; // Your email
                $mail->Password = 'qqka pmit ismp jemi'; // Your email password (or App password if 2FA enabled)

                // TLS or SSL encryption based on your preference
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SSL encryption
                $mail->Port = 465;  // SSL port

                // Debugging: Enable detailed output for PHPMailer
                $mail->SMTPDebug = 3; // Set to 3 for more verbose debugging

                // Recipients
                $mail->setFrom('dormease2024@gmail.com', 'DormEase');
                $mail->addAddress($email, $dbFirstName);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $resetLink = "http://localhost/dormease/reset?token=" . $reset_token;
                $mail->Body = "Hi " . $dbFirstName . ",<br><br>Click the link below to reset your password:<br><a href='" . $resetLink . "'>" . $resetLink . "</a><br><br>If you did not request a password reset, please ignore this email.";

                // Send the email
                if ($mail->send()) {
                    // Redirect with success message
                    header("Location: forgot?reset-success");
                    exit();
                } else {
                    // If PHPMailer fails
                    header("Location: forgot?reset-fail");
                    exit();
                }
            } catch (Exception $e) {
                // If PHPMailer fails, display the error message
                header("Location: forgot?reset-fail");
                exit();
            }
        } else {
            // Email not found
            header("Location: forgot?email-not-found");
            exit();
        }
    } else {
        // Invalid email format
        header("Location: forgot?invalid-email");
        exit();
    }
}
?>

<!-- HTML form for forgot password -->
<section class="p-3 p-md-4 p-xl-5 min-vh-100">
    <div class="container" style="padding-top: 80px;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4"> <!-- Adjusted column size for responsiveness -->
                <!-- Right Column for Forgot Password Card -->
                <div class="card border-light-subtle shadow-sm">
                    <div class="card-body p-3 p-md-4 p-xl-5">
                        <h2 class="fw-bold text-center mb-3">Forgot Password</h2>
                        <form method="post">
                            <!-- Display error or success messages -->
                            <?php
                            if (isset($_GET['reset-success'])) {
                                echo '<div class="alert alert-success">A password reset link has been sent to your email.</div>';
                            } elseif (isset($_GET['reset-fail'])) {
                                echo '<div class="alert alert-danger">Failed to send reset email. Please try again later.</div>';
                            } elseif (isset($_GET['email-not-found'])) {
                                echo '<div class="alert alert-danger">Email not found.</div>';
                            } elseif (isset($_GET['invalid-email'])) {
                                echo '<div class="alert alert-danger">Invalid email format.</div>';
                            }
                            ?>

                            <div class="row gy-3 overflow-hidden">
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" name="email" id="email" autocomplete="on" placeholder="name@example.com" required>
                                        <label for="email" class="form-label">Email</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button class="btn btn-dark btn-lg" name="submit" type="submit">Send Reset Link</button>
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