<?php
if (!isset($_SESSION["a_ID"])) {
    header("location:login");
    exit();
}

// Fetch all tenants with u_Account_Type = 1 (tenants)
$tenantsQuery = "SELECT u_FName, u_MName, u_LName, u_ContactNumber, u_Email, u_Balance, u_BalanceStatus FROM user WHERE u_Account_Type = 1";
$tenantsResult = $con->query($tenantsQuery);
?>

<div class="container pt-5 mt-5">
    <div class="row mt-4">
        <div class="col-12">
            <h5>Tenants</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact Number</th>
                        <th>Email</th>
                        <th>Balance</th>
                        <th>Balance Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tenantsResult->num_rows > 0): ?>
                        <?php while ($row = $tenantsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['u_FName'] . ' ' . $row['u_MName'] . ' ' . $row['u_LName']; ?></td>
                                <td><?php echo $row['u_ContactNumber']; ?></td>
                                <td><?php echo $row['u_Email']; ?></td>
                                <td><?php echo number_format($row['u_Balance'], 2); ?></td>
                                <td><?php echo $row['u_BalanceStatus']; ?></td>
                                <td>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editListingModal<?= $dorm['d_ID']; ?>">Edit</button>
                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteListingModal<?= $dorm['d_ID']; ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No tenants found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>