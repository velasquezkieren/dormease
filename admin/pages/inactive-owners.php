<?php
if (!isset($_SESSION["a_ID"])) {
    header("location:login");
    exit();
}

// Fetch all pending owners with u_Account_Type = 2
$inactiveOwnerQuery = "SELECT u_ID, u_FName, u_MName, u_LName, u_ContactNumber, u_Email, u_VerificationPicture FROM user WHERE u_Account_Type = 2";
$inactiveOwnerResult = $con->query($inactiveOwnerQuery);
?>

<div class="container pt-5 mt-5">
    <div class="row mt-4">
        <div class="col-12">
            <h5>Pending Owners</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact Number</th>
                        <th>Email</th>
                        <th>Verification Picture</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($inactiveOwnerResult->num_rows > 0): ?>
                        <?php while ($row = $inactiveOwnerResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['u_FName'] . ' ' . $row['u_MName'] . ' ' . $row['u_LName']; ?></td>
                                <td><?php echo $row['u_ContactNumber']; ?></td>
                                <td><?php echo $row['u_Email']; ?></td>
                                <td class="d-flex justify-content-center align-items-center">
                                    <?php
                                    // Construct the image path based on the user ID and image name
                                    $imagePath = '../upload_verify/' . $row['u_ID'] . '/' . $row['u_VerificationPicture'];
                                    ?>
                                    <button class="btn" data-bs-toggle="modal" data-bs-target="#imageModal<?php echo $row['u_ID']; ?>">
                                        <img src="<?php echo $imagePath; ?>" alt="<?php echo $row['u_VerificationPicture']; ?>" style="width: 100px; height: auto;">
                                    </button>
                                    <!-- Modal for Image Carousel -->
                                    <div class="modal" id="imageModal<?php echo $row['u_ID']; ?>" tabindex="-1" aria-labelledby="imageModalLabel<?php echo $row['u_ID']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="imageModalLabel<?php echo $row['u_ID']; ?>">Verification Picture</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body d-flex justify-content-center align-items-center">
                                                    <img src="<?php echo $imagePath; ?>" alt="<?php echo $row['u_VerificationPicture']; ?>" class="img-fluid" style="max-height: 600px; max-width: 100%;">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <form method="POST" action="user-approval" class="d-inline">
                                        <input type="hidden" name="u_ID" value="<?php echo $row['u_ID']; ?>">
                                        <button type="submit" name="action" value="accept" class="btn btn-success">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill-check" viewBox="0 0 16 16">
                                                <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                                                <path d="M2 13c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4" />
                                            </svg>
                                            Accept
                                        </button>
                                        <button type="submit" name="action" value="deny" class="btn btn-danger">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill-dash" viewBox="0 0 16 16">
                                                <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7M11 12h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1 0-1m0-7a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                                                <path d="M2 13c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4" />
                                            </svg>
                                            Deny
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No pending owners found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>