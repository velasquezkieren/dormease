<?php
if (isset($_POST['schedule'])) {
    // Collect form data
    $visitorID = $_POST['visitorID'];
    $landlordID = $_POST['landlordID'];
    $visitDateTime = $_POST['visitDateTime'];
    $d_ID = $_POST['d_ID'];
    $v_Status = 2;

    // Generate a unique ID for the visit (you can adjust this as needed)
    $visitID = uniqid('v_', true);

    // Prepare an SQL statement to prevent SQL injection
    $stmt = $con->prepare("INSERT INTO visit (v_ID, v_Visitor, v_Landlord, v_DateTime, v_Dormitory, v_Status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $visitID, $visitorID, $landlordID, $visitDateTime, $d_ID, $v_Status);

    // Execute the statement
    if ($stmt->execute()) {
        header("location: property?d_ID" . $d_ID);
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $con->close();
}

?>


<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active card-title" id="schedule-visit-tab" data-bs-toggle="tab" href="#schedule-visit" role="tab" aria-controls="schedule-visit" aria-selected="true">Schedule a Visit</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link card-title" id="inquiry-tab" data-bs-toggle="tab" href="#inquiry" role="tab" aria-controls="inquiry" aria-selected="false">Inquiry</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <!-- Schedule a Visit Tab -->
            <div class="tab-pane fade show active" id="schedule-visit" role="tabpanel" aria-labelledby="schedule-visit-tab">
                <form action="" method="POST">
                    <input type="hidden" name="visitorID" value="<?php echo $loggedInUserID; ?>">
                    <input type="hidden" name="landlordID" value="<?php echo $d_Owner_ID; ?>">
                    <input type="hidden" name="d_ID" value="<?php echo $d_ID; ?>">
                    <div class="mb-3 mt-2">
                        <label for="visitDateTime" class="form-label">Date and Time</label>
                        <input type="datetime-local" class="form-control" id="visitDateTime" name="visitDateTime" required>
                    </div>
                    <button type="submit" name="schedule" class="btn btn-primary">Schedule Visit</button>
                </form>
            </div>

            <!-- Inquiry Tab -->
            <div class="tab-pane fade" id="inquiry" role="tabpanel" aria-labelledby="inquiry-tab">
                <form action="submit_inquiry.php" method="POST">
                    <input type="hidden" name="visitorID" value="<?php echo $loggedInUserID; ?>">
                    <input type="hidden" name="landlordID" value="<?php echo $d_Owner_ID; ?>">
                    <div class="mb-3 mt-2">
                        <label for="inquiryMessage" class="form-label">Message</label>
                        <textarea class="form-control" id="inquiryMessage" name="inquiryMessage" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Inquiry</button>
                </form>
            </div>
        </div>
    </div>
</div>