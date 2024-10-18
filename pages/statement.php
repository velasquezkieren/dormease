<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

// Only allow tenants (Account Type 1) to access this page
if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] != 1) {
    header("location: profile?u_ID=" . $_SESSION['u_ID']);
    die();
}

// Fetch tenant ID from session
$tenant_id = $_SESSION['u_ID'];

// Fetch balance and transactions for the tenant
$query = "SELECT * FROM ledger WHERE l_Recipient = ? ORDER BY l_Date DESC";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$transactions_result = $stmt->get_result();
$transactions = $transactions_result->fetch_all(MYSQLI_ASSOC);

// Calculate total balance (assuming 'income' adds to balance and 'expense' deducts from balance)
$balance_query = "SELECT 
                    SUM(CASE WHEN l_Type = 1 THEN l_Amount ELSE -l_Amount END) AS balance 
                  FROM ledger 
                  WHERE l_Recipient = ?";
$balance_stmt = $con->prepare($balance_query);
$balance_stmt->bind_param("i", $tenant_id);
$balance_stmt->execute();
$balance_result = $balance_stmt->get_result();
$balance_row = $balance_result->fetch_assoc();
$balance = $balance_row['balance'] ?? 0.00;
?>

<!-- HTML Section -->
<div class="container pt-5 min-vh-100" style="margin-top: 100px;">
    <div class="row">
        <!-- Sidebar nav -->
        <div class="col-md-4">
            <?php include('sidebar_profile.php'); ?>
        </div>

        <!-- SOA Details -->
        <div class="col-md-8">
            <h1 class="mb-4">Statement of Account</h1>
            <p><strong>Your current balance:</strong> ₱<?= htmlspecialchars(number_format($balance, 2)) ?></p>

            <!-- Ledger Transactions -->
            <table class="table table-striped table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transactions) > 0): ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?= htmlspecialchars($transaction['l_Description']) ?></td>
                                <td>
                                    <?php
                                    // Display amount with currency formatting
                                    $amount = $transaction['l_Amount'];
                                    if ($transaction['l_Type'] == 0) {
                                        echo "-₱" . htmlspecialchars(number_format($amount, 2));
                                    } else {
                                        echo "₱" . htmlspecialchars(number_format($amount, 2));
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($transaction['l_Type'] == 0 ? 'Expense' : 'Income') ?></td>
                                <td><?= htmlspecialchars($transaction['l_Date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>