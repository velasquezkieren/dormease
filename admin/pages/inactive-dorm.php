<?php
if (!isset($_SESSION["a_ID"])) {
    header("location:login");
    exit();
}

// Accept dormitory
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['d_ID']) && isset($_POST['action'])) {
    $dorm_id = mysqli_real_escape_string($con, $_POST['d_ID']);
    $action = $_POST['action'];

    if ($action === 'accept') {
        // Update the dormitory's registration status to 1 (active)
        $updateQuery = "UPDATE dormitory SET d_RegistrationStatus = 1 WHERE d_ID = '$dorm_id'";
    } elseif ($action === 'deny') {
        // Update the dormitory's registration status to 2 (denied)
        $updateQuery = "UPDATE dormitory SET d_RegistrationStatus = 2 WHERE d_ID = '$dorm_id'";
    }

    if ($con->query($updateQuery) === TRUE) {
        header("Location: inactive-dorm?status=success");
        exit();
    } else {
        header("Location: inactive-dorm?status=error");
        exit();
    }
}

// Fetch all dormitories with d_RegistrationStatus = 0
$dormitoriesInactiveQuery = "SELECT * FROM dormitory WHERE d_RegistrationStatus = 0";
$dormsResult = $con->query($dormitoriesInactiveQuery);
?>

<!-- Output here all the d_RegistrationStatus 0 -->
<div class="container pt-5 mt-5">
    <div class="row mt-4">
        <div class="col-12">
            <h5>Inactive Dormitories</h5>
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
                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="d_ID" value="<?= htmlspecialchars($dorm['d_ID']); ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="btn btn-success">Accept</button>
                                    </form>
                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="d_ID" value="<?= htmlspecialchars($dorm['d_ID']); ?>">
                                        <input type="hidden" name="action" value="deny">
                                        <button type="submit" class="btn btn-danger">Deny</button>
                                    </form>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No inactive dormitories found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>