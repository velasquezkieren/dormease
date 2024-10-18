<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] !== 0) {
    header("location: profile?u_ID=" . $_SESSION['u_ID']);
    die();
}

// Get the logged-in owner's ID
$ownerID = $_SESSION['u_ID'];

// Query to fetch tenants who are currently occupying rooms in the owner's dorms
$tenant_query = "
    SELECT u.u_ID, u.u_FName, u.u_LName 
    FROM occupancy o
    JOIN room r ON o.o_Room = r.r_ID
    JOIN dormitory d ON r.r_Dormitory = d.d_ID
    JOIN user u ON o.o_Occupant = u.u_ID
    WHERE d.d_Owner = '$ownerID' AND o.o_Status = 1"; // assuming o_Status = 1 means currently occupied

$tenant_result = mysqli_query($con, $tenant_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $biller = $_SESSION['u_ID']; // assuming the logged-in user is the biller
    $recipient = $_POST['tenant_id'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];

    // Validate input
    if (empty($recipient) || empty($description) || empty($amount) || empty($type)) {
        echo "<p style='color:red;'>All fields are required.</p>";
    } else {
        // Begin transaction
        mysqli_begin_transaction($con);

        // Generate a unique ID for the ledger entry
        $ledgerId = uniqid('l_');

        // Determine the type value
        $typeValue = ($type === 'income') ? 1 : 0;

        // Insert new transaction
        $insert_query = "INSERT INTO ledger (l_ID, l_Biller, l_Recipient, l_Date, l_Description, l_Amount, l_Type) 
                         VALUES ('$ledgerId', '$biller', '$recipient', NOW(), '$description', '$amount', '$typeValue')";

        if (mysqli_query($con, $insert_query)) {
            // Update tenant's balance
            $balance_change = ($type === 'income') ? $amount : -$amount;
            $update_balance_query = "UPDATE user SET u_Balance = u_Balance + $balance_change WHERE u_ID = '$recipient'";

            if (mysqli_query($con, $update_balance_query)) {
                // Commit transaction
                mysqli_commit($con);
                header("Location: ledger");
                exit;
            } else {
                mysqli_rollback($con);
                echo "<p style='color:red;'>Failed to update tenant's balance: " . mysqli_error($con) . "</p>";
            }
        } else {
            mysqli_rollback($con);
            echo "<p style='color:red;'>Failed to add transaction: " . mysqli_error($con) . "</p>";
        }
    }
}
?>

<!-- HTML -->
<div class="container pt-5" style="margin-top: 100px;">
    <div class="row">
        <div class="col-md-4">
            <?php include('sidebar_profile.php'); ?>
        </div>
        <div class="col-md-8">
            <h2 class="mb-4">Add Transaction</h2>
            <form method="POST" class="needs-validation" novalidate>
                <div class="form-group">
                    <label for="tenant_id">Select Tenant:</label>
                    <select name="tenant_id" class="form-control" required>
                        <option value="" selected disabled>--Select Tenant--</option>
                        <?php
                        if ($tenant_result && mysqli_num_rows($tenant_result) > 0) {
                            while ($tenant = mysqli_fetch_assoc($tenant_result)) {
                                echo "<option value='" . $tenant['u_ID'] . "'>" . $tenant['u_FName'] . " " . $tenant['u_MName'] . " " . $tenant['u_LName'] . "</option>";
                            }
                        } else {
                            echo "<option value='' selected disabled>No tenants found</option>";
                        }
                        ?>
                    </select>
                    <div class="invalid-feedback">Please select a tenant.</div>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <input type="text" name="description" class="form-control" required>
                    <div class="invalid-feedback">Description is required.</div>
                </div>

                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                    <div class="invalid-feedback">Amount is required.</div>
                </div>

                <div class="form-group mb-4">
                    <label for="type">Type:</label>
                    <select name="type" class="form-control" required>
                        <option value="" disabled selected>--Select Type--</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                    <div class="invalid-feedback">Please select a type.</div>
                </div>


                <a href="ledger" class="btn btn-secondary">Back to Dashboard</a>
                <button type="submit" class="btn btn-dark">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                    </svg>
                    Add Transaction
                </button>
            </form>
        </div>
    </div>
</div>