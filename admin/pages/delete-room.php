<?php
// Assuming you've already connected to your database
if (isset($_POST['delete_room'])) {
    // Get the room name from the POST request
    $r_Name = $_POST['r_Name'];

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $con->prepare("DELETE FROM room WHERE r_Name = ?");
    if ($stmt) {
        // Bind the parameter
        $stmt->bind_param("s", $r_Name);

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
