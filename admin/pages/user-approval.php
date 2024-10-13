<?php
if (!isset($_SESSION["a_ID"])) {
    header("location:login");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u_ID = $_POST['u_ID'];
    $action = $_POST['action'];

    if ($action === 'accept') {
        // Accept the owner
        $updateQuery = "UPDATE user SET u_Account_Type = 0, u_RegistrationStatus = 1 WHERE u_ID = ?";
        $stmt = $con->prepare($updateQuery);
        $stmt->bind_param("s", $u_ID);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Success message or redirect
            header("Location: inactive-owners?status=accepted");
            exit();
        } else {
            // Handle error
            echo "Error accepting owner.";
        }
    } elseif ($action === 'deny') {
        // Deny the owner
        $selectQuery = "SELECT u_VerificationPicture FROM user WHERE u_ID = ?";
        $stmt = $con->prepare($selectQuery);
        $stmt->bind_param("s", $u_ID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $userFolderPath = '../upload_verify/' . $u_ID;
            $picturePath = $userFolderPath . '/' . $row['u_VerificationPicture'];

            // Delete the verification picture
            if (file_exists($picturePath)) {
                unlink($picturePath);
            }

            // Function to recursively delete the folder
            function deleteDirectory($dir)
            {
                if (!file_exists($dir)) {
                    return true;
                }

                if (!is_dir($dir)) {
                    return unlink($dir);
                }

                foreach (scandir($dir) as $item) {
                    if ($item == '.' || $item == '..') {
                        continue;
                    }

                    if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                        return false;
                    }
                }

                return rmdir($dir);
            }

            // Delete the entire folder
            if (file_exists($userFolderPath)) {
                deleteDirectory($userFolderPath);
            }
        }

        // Update user account type to denied
        $updateQuery = "UPDATE user SET u_Account_Type = 1 WHERE u_ID = ?";
        $stmt = $con->prepare($updateQuery);
        $stmt->bind_param("s", $u_ID);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Success message or redirect
            header("Location: inactive-owners?status=denied");
            exit();
        } else {
            // Handle error
            echo "Error denying owner.";
        }
    }

    $stmt->close();
}
$con->close();
