<?php
if (isset($_POST['delete_dormitory'])) {
    // Get the dormitory ID from the form
    $d_ID = $_POST['d_ID'];

    // Fetch the dormitory details to get image names
    $sql = "SELECT d_PicName FROM dormitory WHERE d_ID = '$d_ID'";
    $result = mysqli_query($con, $sql);
    $dormitory = mysqli_fetch_assoc($result);

    if ($dormitory) {
        // Delete associated images
        $images = explode(',', $dormitory['d_PicName']);
        foreach ($images as $image) {
            $filePath = '../upload/' . $d_ID . '/' . $image; // Add the folder name
            if (file_exists($filePath)) {
                unlink($filePath); // Remove the image file
            }
        }

        // Delete the dormitory record
        $sql = "DELETE FROM dormitory WHERE d_ID = '$d_ID'";
        mysqli_query($con, $sql);

        // Delete the folder
        $folderPath = '../upload/' . $d_ID;
        if (is_dir($folderPath)) {
            rmdir($folderPath); // Remove the folder
        }
    }

    // Redirect to profile page
    echo "<script>alert('Listing deleted successfully.'); window.location.href='active-dorm';</script>";
    exit();
}
