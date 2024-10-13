<?php

if (!isset($_SESSION["a_ID"])) {
    header("Location: login");
    exit();
}

// Initialize variables
$dormitoryData = [];

// Fetch transaction details if d_ID is provided
if (isset($_GET['d_ID'])) {
    $d_ID = ($_GET['d_ID']);
    $query = "SELECT * FROM dormitory WHERE d_ID = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $d_ID); // Bind the ID parameter

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $dormitoryData = $result->fetch_assoc();
        } else {
            echo "<div class='alert alert-danger'>Dormitory not found.</div>";
            exit;
        }
    } else {
        echo "<div class='alert alert-danger'>Error fetching dormitory details: " . $stmt->error . "</div>";
        exit;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_dormitory'])) {
    // Get the dormitory ID from the form submission
    $d_ID = ($_POST['d_ID']);

    // Sanitize input
    $d_Name = $con->real_escape_string(trim($_POST['d_Name']));
    $d_Street = $con->real_escape_string(trim($_POST['d_Street']));
    $d_City = $con->real_escape_string(trim($_POST['d_City']));
    $d_ZIPCode = $con->real_escape_string(trim($_POST['d_ZIPCode']));
    $d_Province = $con->real_escape_string(trim($_POST['d_Province']));
    $d_Region = $con->real_escape_string(trim($_POST['d_Region']));
    $d_Availability = isset($_POST['d_Availability']) ? 1 : 0; // Check if available
    $d_Description = $con->real_escape_string(trim($_POST['d_Description']));
    $d_Gender = $con->real_escape_string(trim($_POST['d_Gender']));

    // Validate input
    if (empty($d_Name) || empty($d_Street) || empty($d_City) || empty($d_ZIPCode) || empty($d_Province) || empty($d_Region) || empty($d_Description) || empty($d_Gender)) {
        echo "<p style='color:red;'>All fields are required.</p>";
    } else {
        // Update dormitory in the database
        $update_query = "UPDATE dormitory 
                         SET d_Name = ?, 
                             d_Street = ?, 
                             d_City = ?, 
                             d_ZIPCode = ?, 
                             d_Province = ?, 
                             d_Region = ?, 
                             d_Availability = ?, 
                             d_Description = ?, 
                             d_Gender = ? 
                         WHERE d_ID = ?";

        // Prepare and bind parameters
        $stmt = $con->prepare($update_query);
        if ($stmt) {
            $stmt->bind_param("ssssssssss", $d_Name, $d_Street, $d_City, $d_ZIPCode, $d_Province, $d_Region, $d_Availability, $d_Description, $d_Gender, $d_ID);

            // Execute the statement
            if ($stmt->execute()) {
                header("Location: active-dorm?status=success");
                exit;
            } else {
                echo "<p style='color:red;'>Failed to update dormitory: " . $stmt->error . "</p>";
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
                    <h5 class="card-title">Edit Dormitory: <?php echo htmlspecialchars($dormitoryData['d_Name']); ?></h5>
                    <form method="POST" action="">
                        <input type="hidden" name="d_ID" value="<?= htmlspecialchars($dormitoryData['d_ID'] ?? ''); ?>">

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="d_Name" name="d_Name" placeholder="Dormitory Name" value="<?= htmlspecialchars($dormitoryData['d_Name'] ?? ''); ?>" required>
                            <label for="d_Name">Dormitory Name</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="d_Street" name="d_Street" placeholder="Street" value="<?= htmlspecialchars($dormitoryData['d_Street'] ?? ''); ?>" required>
                            <label for="d_Street">Street</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="d_City" name="d_City" placeholder="City" value="<?= htmlspecialchars($dormitoryData['d_City'] ?? ''); ?>" required>
                            <label for="d_City">City</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="d_ZIPCode" name="d_ZIPCode" placeholder="ZIP Code" value="<?= htmlspecialchars($dormitoryData['d_ZIPCode'] ?? ''); ?>" required>
                            <label for="d_ZIPCode">ZIP Code</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="d_Province" name="d_Province" placeholder="Province" value="<?= htmlspecialchars($dormitoryData['d_Province'] ?? ''); ?>" required>
                            <label for="d_Province">Province</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="d_Region" name="d_Region" placeholder="Region" value="<?= htmlspecialchars($dormitoryData['d_Region'] ?? ''); ?>" required>
                            <label for="d_Region">Region</label>
                        </div>

                        <div class="form-floating mb-3">
                            <select class="form-select" id="d_Gender" name="d_Gender" required>
                                <option value="" disabled selected>Select Gender Restriction</option>
                                <option value="0" <?= ($dormitoryData['d_Gender'] ?? '') == '0' ? 'selected' : ''; ?>>Female Only</option>
                                <option value="1" <?= ($dormitoryData['d_Gender'] ?? '') == '1' ? 'selected' : ''; ?>>Male Only</option>
                                <option value="2" <?= ($dormitoryData['d_Gender'] ?? '') == '2' ? 'selected' : ''; ?>>No Restriction</option>
                            </select>
                            <label for="d_Gender">Gender Specification</label>
                        </div>

                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="d_Description" name="d_Description" placeholder="Description" rows="4" required><?= htmlspecialchars($dormitoryData['d_Description'] ?? ''); ?></textarea>
                            <label for="d_Description">Description</label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="d_Availability" name="d_Availability" value="1" <?= ($dormitoryData['d_Availability'] ?? '') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="d_Availability">Available</label>
                        </div>

                        <div class="modal-footer">
                            <a href="active-dorm" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" name="edit_dormitory" class="btn btn-dark">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>