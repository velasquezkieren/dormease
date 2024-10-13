<?php
if (!isset($_SESSION["a_ID"])) {
    header("location:login");
    exit();
}

// Fetch all active dormitories with d_RegistrationStatus = 1
$dormitoriesQuery = "SELECT * FROM dormitory WHERE d_RegistrationStatus = 1";
$dormsResult = $con->query($dormitoriesQuery);
?>

<!-- Output all active dormitories -->
<div class="container pt-5 mt-5">
    <div class="row mt-4">
        <div class="col-12">
            <h5>Active Dormitories</h5>
            <?php
            if (isset($_GET['status'])) {
                if ($_GET['status'] === 'success') {
                    echo "<div class='alert alert-success'>Dormitory action completed successfully!</div>";
                } elseif ($_GET['status'] === 'error') {
                    echo "<div class='alert alert-danger'>An error occurred. Please try again.</div>";
                }
            }
            ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Owner</th>
                        <th>Location</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($dormsResult && $dormsResult->num_rows > 0) {
                        while ($dorm = $dormsResult->fetch_assoc()) {
                            // Fetch the owner's name securely
                            $owner_ID = mysqli_real_escape_string($con, $dorm['d_Owner']);
                            $owner_query = $con->query("SELECT u_FName, u_MName, u_LName FROM user WHERE u_ID = '$owner_ID'");
                            $owner_data = $owner_query->fetch_assoc();
                            $owner_name = $owner_data ? htmlspecialchars($owner_data['u_FName'] . ' ' . $owner_data['u_MName'] . ' ' . $owner_data['u_LName']) : 'Unknown';

                            // Limit the description to 100 characters
                            $description = substr($dorm['d_Description'], 0, 100);
                            if (strlen($dorm['d_Description']) > 100) {
                                $description .= '...';
                            }
                    ?>
                            <tr>
                                <td><a href="/dormease/property?d_ID=<?= urlencode($dorm['d_ID']); ?>" class="btn"><?= htmlspecialchars($dorm['d_Name']); ?></a></td>
                                <td><?= htmlspecialchars($owner_name); ?></td>
                                <td><?= htmlspecialchars($dorm['d_Street']) . ', ' . htmlspecialchars($dorm['d_City']); ?></td>
                                <td><?= htmlspecialchars($description); ?></td>
                                <td>
                                    <a href="edit-dorm?d_ID=<?= urlencode($dorm['d_ID']); ?>" class="btn btn-dark">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                                            <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z" />
                                        </svg>
                                        Edit Listing
                                    </a>
                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteListingModal<?= $dorm['d_ID']; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                            <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5" />
                                        </svg>
                                        Delete Listing
                                    </button>
                                </td>
                            </tr>

                            <!-- Delete Listing Modal -->
                            <div class="modal" id="deleteListingModal<?= $dorm['d_ID']; ?>" tabindex="-1" aria-labelledby="deleteListingModalLabel<?= $dorm['d_ID']; ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteListingModalLabel">Delete Listing</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this listing? This action cannot be undone.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <form action="delete-dorm?d_ID=<?= urlencode($dorm['d_ID']); ?>" method="POST">
                                                <input type="hidden" name="d_ID" value="<?= $dorm['d_ID']; ?>">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="delete_dormitory" class="btn btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No active dormitories found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$con->close(); // Close the database connection
?>