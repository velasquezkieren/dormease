<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
} elseif (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 0) {
    header('Location: profile');
    exit();
} elseif (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 2) {
    header('Location: profile');
    exit();
}

// Retrieve user ID from session
$user_ID = $_SESSION['u_ID'];

if (isset($_POST['submit'])) {
    // Allowed file types
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    $maxFileSize = 5 * 1024 * 1024; // Maximum file size: 5MB

    // Directory for storing uploaded files
    $uploadDir = "upload_verify/$user_ID/";

    // Create the directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo '<script>alert("Failed to create directory. Check permissions.");</script>';
            exit();
        }
    }

    // Handle the uploaded file
    $file = $_FILES['u_VerificationPicture'];

    // Check if a file was uploaded
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileName = basename($file['name']);
        $fileType = $file['type'];
        $fileSize = $file['size'];

        // Validate file type and size
        if (!in_array($fileType, $allowedTypes)) {
            echo '<script>alert("Invalid file type. Please upload an image in the allowed formats.");</script>';
            exit();
        }
        if ($fileSize > $maxFileSize) {
            echo '<script>alert("File size too large. Maximum file size is 5MB.");</script>';
            exit();
        }

        // Generate a unique name for the uploaded file to avoid overwriting
        $uniqueFileName = uniqid('verification_') . '@dormease@' . $fileName;

        // Define the file path
        $filePath = $uploadDir . $uniqueFileName;

        // Move the file to the specified directory
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Update user account type and verification picture in the database
            $updateStmt = $con->prepare("UPDATE user SET u_Account_Type = ?, u_VerificationPicture = ? WHERE u_ID = ?");
            $accountType = 2; // Set the account type to 2 after verification
            $updateStmt->bind_param('iss', $accountType, $uniqueFileName, $user_ID);

            if ($updateStmt->execute()) {
                header('location:profile?application-success');

                // Reset session variables after successful upload
                $_SESSION['u_Account_Type'] = 2; // Set the updated account type
            } else {
                echo '<script>alert("Error updating account type and verification picture.");</script>';
            }
        } else {
            echo '<script>alert("Error uploading file.");</script>';
        }
    } else {
        echo '<script>alert("No file uploaded or there was an error.");</script>';
    }
}
?>

<!-- HTML form for application -->
<section class="p-3 p-md-4 p-xl-5 min-vh-100">
    <div class="container" style="padding-top: 80px;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8"> <!-- Adjusted for better responsiveness -->
                <!-- Right Column for Application Card -->
                <div class="card border-light-subtle shadow-sm">
                    <div class="card-header text-center">
                        <h2 class="fw-bold mb-3">Apply as Owner</h2>
                    </div>
                    <div class="card-body">
                        <form id="applyAsOwnerForm" method="POST" action="" enctype="multipart/form-data">
                            <!-- Display error messages based on URL error code -->
                            <?php
                            if (isset($_GET['error'])) {
                                echo '<div class="alert alert-danger">An error occurred. Please try again!</div>';
                            } elseif (isset($_GET['application-success'])) {
                                echo '<div class="alert alert-success">Application submitted successfully!</div>';
                            }
                            ?>

                            <div class="mb-3">
                                <label for="u_VerificationPicture" class="form-label">Upload Proof of Ownership (e.g. documents, images)</label>
                                <input class="form-control" type="file" name="u_VerificationPicture" id="u_VerificationPicture" accept=".jpg, .jpeg, .png, .gif" required onchange="previewImages(event)">
                                <div id="image-preview" class="mt-2"></div>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-dark btn-lg w-100" name="submit">Submit Application</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    $(document).ready(function() {
        // Image preview functionality
        $('input[name="u_VerificationPicture"]').on('change', function(event) {
            const imagePreview = $('#image-preview');
            imagePreview.empty(); // Clear previous previews

            const file = event.target.files[0]; // Get the first selected file
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = $('<img>', {
                    src: e.target.result,
                    css: {
                        width: '100px', // Set preview size
                        marginRight: '10px'
                    }
                });
                imagePreview.append(img);
            }
            reader.readAsDataURL(file);
        });
    });
</script>