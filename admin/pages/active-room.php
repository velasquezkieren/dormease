<?php
if (!isset($_SESSION["a_ID"])) {
    header("location:login");
    exit();
}

// Fetch all active rooms with r_RegistrationStatus = 1
$activeRoomQuery = "SELECT r.*, d.d_Name FROM room r JOIN dormitory d ON r.r_Dormitory = d.d_ID WHERE r.r_RegistrationStatus = 1";
$activeRoomResult = $con->query($activeRoomQuery);
?>

<div class="container pt-5 mt-5">
    <div class="row mt-4">
        <div class="col-12">
            <h5>Active Rooms</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Room Name</th>
                        <th>Description</th>
                        <th>Dormitory Name</th>
                        <th>Availability</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($activeRoomResult->num_rows > 0): ?>
                        <?php while ($row = $activeRoomResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['r_Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['r_Description']); ?></td>
                                <td><?php echo htmlspecialchars($row['d_Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['r_Availability']); ?></td>
                                <td>
                                    <a href="edit-room?r_Name=<?php echo urlencode($row['r_Name']); ?>" class="btn btn-dark">
                                        Edit Room
                                    </a>
                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteRoomModal<?php echo urlencode($row['r_Name']); ?>">
                                        Delete Room
                                    </button>

                                    <!-- Delete Room Modal -->
                                    <div class="modal" id="deleteRoomModal<?php echo urlencode($row['r_Name']); ?>" tabindex="-1" aria-labelledby="deleteRoomModalLabel<?php echo urlencode($row['r_Name']); ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteRoomModalLabel<?php echo urlencode($row['r_Name']); ?>">Delete Room: <?php echo htmlspecialchars($row['r_Name']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete the room "<strong><?php echo htmlspecialchars($row['r_Name']); ?></strong>"?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form method="POST" action="delete-room">
                                                        <input type="hidden" name="r_Name" value="<?php echo htmlspecialchars($row['r_Name']); ?>">
                                                        <button type="submit" name="delete_room" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No active rooms found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>