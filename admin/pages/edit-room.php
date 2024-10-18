<?php

if (!isset($_SESSION["a_ID"])) {
    header("Location: login");
    exit();
}

// Initialize variables
$roomData = [];

// Fetch room details if r_ID is provided
if (isset($_GET['r_ID'])) {
    $r_ID = ($_GET['r_ID']);
    $query = "SELECT * FROM room WHERE r_ID = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $r_ID); // Bind the room ID parameter

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $roomData = $result->fetch_assoc();
        } else {
            echo "<div class='alert alert-danger'>Room not found.</div>";
            exit;
        }
    } else {
        echo "<div class='alert alert-danger'>Error fetching room details: " . $stmt->error . "</div>";
        exit;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_room'])) {
    // Get the room ID and name from the form submission
    $r_ID = ($_POST['r_ID']);
    $r_Name = ($_POST['r_Name']);

    // Sanitize input
    $r_Description = $con->real_escape_string(trim($_POST['r_Description']));
    $r_Availability = isset($_POST['r_Availability']) ? 1 : 0; // Check if available

    // Validate input
    if (empty($r_Description)) {
        echo "<p style='color:red;'>Description is required.</p>";
    } else {
        // Update room in the database
        $update_query = "UPDATE room 
                         SET r_Description = ?, 
                             r_Availability = ? 
                         WHERE r_ID = ?";

        // Prepare and bind parameters
        $stmt = $con->prepare($update_query);
        if ($stmt) {
            $stmt->bind_param("sis", $r_Description, $r_Availability, $r_ID);

            // Execute the statement
            if ($stmt->execute()) {
                header("Location: active-room?status=success");
                exit;
            } else {
                echo "<p style='color:red;'>Failed to update room: " . $stmt->error . "</p>";
            }
        } else {
            echo "<p style='color:red;'>Database error: " . $con->error . "</p>";
        }
    }
}
?>

<div class="container pt-5 mt-5">
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Edit Room: <?php echo htmlspecialchars($roomData['r_Name']); ?></h5>
                    <form method="POST" action="">
                        <input type="hidden" name="r_ID" value="<?= htmlspecialchars($roomData['r_ID'] ?? ''); ?>">
                        <input type="hidden" name="r_Name" value="<?= htmlspecialchars($roomData['r_Name'] ?? ''); ?>">

                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="r_Description" name="r_Description" placeholder="Description" rows="4" required><?= htmlspecialchars($roomData['r_Description'] ?? ''); ?></textarea>
                            <label for="r_Description">Description</label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="r_Availability" name="r_Availability" value="1" <?= ($roomData['r_Availability'] ?? '') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="r_Availability">Available</label>
                        </div>

                        <div class="modal-footer">
                            <a href="active-room" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" name="edit_room" class="btn btn-dark">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>