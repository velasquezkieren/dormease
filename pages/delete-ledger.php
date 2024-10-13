<?php
// Handle deletion
if (isset($_POST['delete'])) {
    $ledger_id = $_POST['ledger_id'];

    // Ensure that the ledger_id is set
    if (!empty($ledger_id)) {
        // Prepare the delete statement
        $delete_query = "DELETE FROM ledger WHERE l_ID = ?";
        $stmt = mysqli_prepare($con, $delete_query);
        mysqli_stmt_bind_param($stmt, 's', $ledger_id);

        // Execute the statement
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
