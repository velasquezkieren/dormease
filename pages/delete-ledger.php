<?php
// Handle deletion
if (isset($_POST['delete'])) {
    $ledger_id = $_POST['ledger_id'];

    // Ensure that the ledger_id is set
    if (!empty($ledger_id)) {
        // Fetch the ledger entry to get the amount and recipient
        $select_query = "SELECT l_Recipient, l_Biller, l_Amount, l_Type FROM ledger WHERE l_ID = ?";
        $stmt = mysqli_prepare($con, $select_query);
        mysqli_stmt_bind_param($stmt, 's', $ledger_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $recipient, $biller, $amount, $type);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // Determine whose balance to update
        $user_to_update = ($type == 1) ? $recipient : $biller;

        // Update the user's balance (subtract the amount)
        $update_balance_query = "UPDATE user SET u_Balance = u_Balance - ? WHERE u_ID = ?";
        $stmt = mysqli_prepare($con, $update_balance_query);
        mysqli_stmt_bind_param($stmt, 'ds', $amount, $user_to_update);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Delete the ledger entry
        $delete_query = "DELETE FROM ledger WHERE l_ID = ?";
        $stmt = mysqli_prepare($con, $delete_query);
        mysqli_stmt_bind_param($stmt, 's', $ledger_id);

        // Execute the delete statement
        if (mysqli_stmt_execute($stmt)) {
            header("location: ledger?delete-success=1");
            exit; // Ensure no further code is executed after redirection
        } else {
            header("location: ledger?error=1");
            exit;
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        header("location: ledger?error=1");
        exit;
    }
}
