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

// Check if the form was submitted
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

        // Insert new transaction using mysqli_query
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
                        $tenant_query = "SELECT u_ID, u_FName, u_MName, u_LName FROM user WHERE u_Account_Type = 1";
                        $result = mysqli_query($con, $tenant_query);

                        if (!$result) {
                            echo "<script>alert(`Error fetching tenants: " . mysqli_error($con) . " `)</script>";
                        }

                        while ($tenant = mysqli_fetch_assoc($result)) {
                            echo "<option value='" . htmlspecialchars($tenant['u_ID']) . "'>" .
                                htmlspecialchars($tenant['u_FName']) . " " .
                                htmlspecialchars($tenant['u_MName']) . " " .
                                htmlspecialchars($tenant['u_LName']) .
                                "</option>";
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

                <button type="submit" class="btn btn-primary">Add Transaction</button>
                <a href="ledger" class="btn btn-secondary">Back to Dashboard</a>
            </form>
        </div>
    </div>
</div>