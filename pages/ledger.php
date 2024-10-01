<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login&auth-required");
    die();
}

if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] !== 0) {
    header("location: profile?u_ID=" . $_SESSION['u_ID']);
    die();
}

// Handle deletion
if (isset($_POST['delete'])) {
    $ledger_id = $_POST['ledger_id'];

    // Prepare the delete statement
    $delete_query = "DELETE FROM ledger WHERE l_ID = ?";
    $stmt = mysqli_prepare($con, $delete_query);
    mysqli_stmt_bind_param($stmt, 'i', $ledger_id);

    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        header("location: ledger?delete-success=1");
    } else {
        header("location: ledger?error=1");
    }

    // Close the statement
    mysqli_stmt_close($stmt);
}

// Prepare and execute the query to fetch transactions
$query = "SELECT ledger.*, CONCAT(user.u_FName, ' ', user.u_LName) AS tenant_name 
          FROM ledger 
          JOIN user ON ledger.l_Recipient = user.u_ID 
          ORDER BY ledger.l_Date DESC";

$transactions_query = mysqli_query($con, $query);

if ($transactions_query) {
    $transactions = mysqli_fetch_all($transactions_query, MYSQLI_ASSOC);
} else {
    echo "Error: " . mysqli_error($con);
}

?>

<!-- HTML -->
<div class="container pt-5" style="margin-top: 100px;">
    <div class="row">
        <!-- Sidebar included here -->
        <div class="col-md-4">
            <?php include('sidebar_profile.php'); ?>
        </div>

        <!-- Main Ledger Content -->
        <div class="col-md-8">
            <h2>All Transactions</h2>
            <?php
            // Success/Error Feedback
            if (isset($_GET['edit-success'])) {
                echo '<div class="alert alert-success">Transaction edited successfully!</div>';
            } elseif (isset($_GET['delete-success'])) {
                echo '<div class="alert alert-success">Transaction deleted successfully!</div>';
            } elseif (isset($_GET['error'])) {
                echo '<div class="alert alert-danger">An error occurred. Please try again.</div>';
            }
            ?>
            <table class="table table-striped table-bordered mt-4">
                <tr>
                    <th>Tenant Name</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td style="display: none;"><?= htmlspecialchars($transaction['l_ID']) ?></td> <!-- Hidden Ledger ID -->
                            <td><?= htmlspecialchars($transaction['tenant_name']) ?></td>
                            <td><?= htmlspecialchars($transaction['l_Description']) ?></td>
                            <td>
                                <?php
                                if ($transaction['l_Type'] == 0) {
                                    echo "-₱" . htmlspecialchars(number_format($transaction['l_Amount'], 2));
                                } else {
                                    echo "₱" . htmlspecialchars(number_format($transaction['l_Amount'], 2));
                                }
                                ?>
                            </td>
                            <td class="<?= htmlspecialchars($transaction['l_Type'] == 0 ? 'expense' : 'income') ?>">
                                <?= ucfirst(htmlspecialchars($transaction['l_Type'] == 0 ? 'expense' : 'income')) ?>
                            </td>
                            <td><?= htmlspecialchars($transaction['l_Date']) ?></td>
                            <td>
                                <a class="btn btn-secondary edit-btn" href="edit-ledger?l_ID=<?= htmlspecialchars($transaction['l_ID']) ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                                        <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z" />
                                    </svg>
                                    Edit
                                </a>
                                <button type="button" class="btn btn-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteLedgerModal" data-id="<?= htmlspecialchars($transaction['l_ID']) ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                        <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5" />
                                    </svg>
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </table>
            <a href="add-ledger" class="btn btn-dark float-end">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                    <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                    <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                </svg>
                Add New Transaction
            </a>
        </div>
    </div>
</div>

<!-- Delete Ledger Confirmation Modal -->
<div class="modal" id="deleteLedgerModal" tabindex="-1" aria-labelledby="deleteLedgerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteLedgerModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this transaction? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="">
                    <input type="hidden" name="ledger_id" id="delete_ledger_id">
                    <button type="submit" name="delete" class="btn btn-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                            <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5" />
                        </svg>
                        Delete
                    </button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // When delete button is clicked
        $('.delete-btn').on('click', function() {
            var ledgerId = $(this).data('id'); // Get the ledger ID from data attribute
            $('#delete_ledger_id').val(ledgerId); // Set the value in the hidden input
        });
    });
</script>