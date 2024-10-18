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

// Fetch transaction details if l_ID is provided
if (isset($_GET['l_ID'])) {
    $ledger_id = $_GET['l_ID'];

    // Fetch the transaction from ledger
    $query = "SELECT * FROM ledger WHERE l_ID = '$ledger_id'";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $transaction = mysqli_fetch_assoc($result);
    } else {
        echo "<p style='color:red;'>Transaction not found.</p>";
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient = $_POST['tenant_id'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];

    // Validate input
    if (empty($recipient) || empty($description) || empty($amount) || empty($type)) {
        echo "<p style='color:red;'>All fields are required.</p>";
    } else {
        // Begin balance calculation

        // Original balance change (before update)
        $original_balance_change = ($transaction['l_Type'] == 1) ? $transaction['l_Amount'] : -$transaction['l_Amount'];

        // New balance change (after update)
        $new_balance_change = ($type === 'income') ? $amount : -$amount;

        // Calculate the difference
        $difference = $new_balance_change - $original_balance_change;

        // Update the ledger transaction
        $typeValue = ($type === 'income') ? 1 : 0;
        $update_query = "UPDATE ledger 
                         SET l_Recipient = '$recipient', 
                             l_Description = '$description', 
                             l_Amount = '$amount', 
                             l_Type = '$typeValue' 
                         WHERE l_ID = '$ledger_id'";

        if (mysqli_query($con, $update_query)) {
            // Update the tenant's balance
            $update_balance_query = "UPDATE user SET u_Balance = u_Balance + '$difference' WHERE u_ID = '$recipient'";
            mysqli_query($con, $update_balance_query);

            // Redirect to ledger page with success message
            header("Location: ledger?edit-success=true");
            exit;
        } else {
            echo "<p style='color:red;'>Failed to update transaction: " . mysqli_error($con) . "</p>";
        }
    }
}
?>

<!-- HTML Section -->
<div class="container pt-5 min-vh-100" style="margin-top: 100px;">
    <div class="row">
        <!-- Sidebar included here -->
        <div class="col-md-4">
            <?php include('sidebar_profile.php'); ?>
        </div>

        <!-- Main Ledger Content -->
        <div class="col-md-8">
            <h2>Edit Transaction</h2>
            <form method="POST" class="needs-validation" novalidate>
                <div class="form-group">
                    <label for="tenant_id">Select Tenant:</label>
                    <select name="tenant_id" class="form-control" required>
                        <option value="">--Select Tenant--</option>
                        <?php
                        $tenant_query = "SELECT u_ID, u_FName, u_MName, u_LName FROM user WHERE u_Account_Type = 1";
                        $result = mysqli_query($con, $tenant_query);

                        while ($tenant = mysqli_fetch_assoc($result)) {
                            $selected = ($tenant['u_ID'] === $transaction['l_Recipient']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($tenant['u_ID']) . "' $selected>" .
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
                    <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($transaction['l_Description']) ?>" required>
                    <div class="invalid-feedback">Description is required.</div>
                </div>

                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="<?= htmlspecialchars($transaction['l_Amount']) ?>" required>
                    <div class="invalid-feedback">Amount is required.</div>
                </div>

                <div class="form-group mb-4">
                    <label for="type">Type:</label>
                    <select name="type" class="form-control" required>
                        <option value="" disabled>--Select Type--</option>
                        <option value="income" <?= $transaction['l_Type'] == 1 ? 'selected' : '' ?>>Income</option>
                        <option value="expense" <?= $transaction['l_Type'] == 0 ? 'selected' : '' ?>>Expense</option>
                    </select>
                    <div class="invalid-feedback">Please select a type.</div>
                </div>

                <button type="submit" class="btn btn-dark">Update Transaction</button>
                <a href="ledger" class="btn btn-secondary">Back to Dashboard</a>
            </form>
        </div>
    </div>
</div>