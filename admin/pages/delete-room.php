<?php
// Assuming you've already connected to your database
if (isset($_POST['delete_room'])) {
    // Get the room ID from the POST request
    $r_ID = $_POST['r_ID'];

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $con->prepare("DELETE FROM room WHERE r_ID = ?");
    if ($stmt) {
        // Bind the parameter
        $stmt->bind_param("s", $r_ID);

        // Execute the statement
        if ($stmt->execute()) {
            echo "<script>alert('Room deleted successfully.'); window.location.href='active-room';</script>";
        } else {
            echo "Error deleting room: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $con->error;
    }
}
