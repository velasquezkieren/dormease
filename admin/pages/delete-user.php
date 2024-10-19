<?php
// Ensure the admin is logged in
if (!isset($_SESSION["a_ID"])) {
    header("Location: login");
    exit();
}

// Ensure the u_ID parameter is provided in the URL
if (!isset($_GET['u_ID']) || empty($_GET['u_ID'])) {
    header("Location: dashboard"); // Redirect if u_ID is missing
    exit();
}

$user_ID = $_GET['u_ID']; // Get the user ID from the URL

if (isset($_POST['delete-user'])) {
    $con->begin_transaction();
    try {
        // Fetch user profile picture
        $image_query = $con->prepare("SELECT u_Picture, u_VerificationPicture FROM user WHERE u_ID = ?");
        $image_query->bind_param("s", $user_ID);
        $image_query->execute();
        $image_data = $image_query->get_result()->fetch_assoc();
        $picName = $image_data['u_Picture'];
        $verifyPic = $image_data['u_VerificationPicture'];

        // Delete user profile picture if not default
        if ($picName && $picName !== 'default_avatar.png') {
            $file_path = '../user_avatar/' . $picName;
            if (file_exists($file_path) && !unlink($file_path)) {
                throw new Exception("Failed to delete profile picture.");
            }
        }

        // Delete verification picture if exists
        if ($verifyPic) {
            $file_path = '../upload_verify/' . $user_ID . '/' . $verifyPic;
            if (file_exists($file_path) && !unlink($file_path)) {
                throw new Exception("Failed to delete verification picture.");
            }
            // Check if the verification directory is empty and delete it
            $directoryPath = '../upload_verify/' . $user_ID . '/';
            if (is_dir($directoryPath) && count(scandir($directoryPath)) == 2) {
                if (!rmdir($directoryPath)) {
                    throw new Exception("Failed to delete directory: " . $directoryPath);
                }
            }
        }

        // Delete associated dormitory listings
        $dorms_query = $con->prepare("SELECT d_ID, d_PicName FROM dormitory WHERE d_Owner = ?");
        $dorms_query->bind_param("s", $user_ID);
        $dorms_query->execute();
        $dorms = $dorms_query->get_result();

        while ($dorm = $dorms->fetch_assoc()) {
            $dorm_ID = $dorm['d_ID'];
            $dorm_images = explode(',', $dorm['d_PicName']);
            $directoryPath = '../upload/' . $dorm_ID . '/'; // Directory for dormitory images

            // Delete images from the server
            foreach ($dorm_images as $image) {
                $file_path = $directoryPath . $image;
                if (file_exists($file_path) && !unlink($file_path)) {
                    throw new Exception("Failed to delete dormitory image: " . $file_path);
                }
            }

            // Remove the directory if empty after deletion
            if (is_dir($directoryPath) && count(scandir($directoryPath)) == 2) {
                rmdir($directoryPath);
            }
        }

        // Delete dormitory listings
        $delete_dorm_query = $con->prepare("DELETE FROM dormitory WHERE d_Owner = ?");
        $delete_dorm_query->bind_param("s", $user_ID);
        $delete_dorm_query->execute();

        // Delete user-related records in other tables
        $delete_ledger_query = $con->prepare("DELETE FROM ledger WHERE l_Biller = ? OR l_Recipient = ?");
        $delete_ledger_query->bind_param("ss", $user_ID, $user_ID);
        $delete_ledger_query->execute();

        $delete_occupancy_query = $con->prepare("DELETE FROM occupancy WHERE o_Occupant = ?");
        $delete_occupancy_query->bind_param("s", $user_ID);
        $delete_occupancy_query->execute();

        $delete_room_query = $con->prepare("DELETE FROM room WHERE r_Dormitory IN (SELECT d_ID FROM dormitory WHERE d_Owner = ?)");
        $delete_room_query->bind_param("s", $user_ID);
        $delete_room_query->execute();

        // Delete the user
        $delete_user_query = $con->prepare("DELETE FROM user WHERE u_ID = ?");
        $delete_user_query->bind_param("s", $user_ID);
        $delete_user_query->execute();

        $con->commit();

        header("Location: dashboard");
        exit();
    } catch (Exception $e) {
        // Rollback if an error is encountered
        $con->rollback();
        echo "<script>alert('Error deleting account: " . $e->getMessage() . "');</script>";
    }
}
