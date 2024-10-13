<?php
if (!isset($_SESSION["a_ID"])) {
    header("location:login");
    exit();
}

// Fetch all owners with u_Account_Type = 0 (owners)
$ownersQuery = "SELECT u_ID, u_FName, u_MName, u_LName, u_ContactNumber, u_Email FROM user WHERE u_Account_Type = 0";
$ownersResult = $con->query($ownersQuery);
?>

<div class="container pt-5 mt-5">
    <div class="row mt-4">
        <div class="col-12">
            <h5>Registered Owners</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact Number</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ownersResult->num_rows > 0): ?>
                        <?php while ($row = $ownersResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['u_FName'] . ' ' . $row['u_MName'] . ' ' . $row['u_LName']; ?></td>
                                <td><?php echo $row['u_ContactNumber']; ?></td>
                                <td><?php echo $row['u_Email']; ?></td>
                                <td>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editListingModal<?= $dorm['d_ID']; ?>">Edit</button>
                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteListingModal<?= $dorm['d_ID']; ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No owners found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>