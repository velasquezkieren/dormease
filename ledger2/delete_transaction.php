<?php
// delete_transaction.php
session_start();
include 'config.php';

checkUserRole('owner');

if (isset($_GET['id'])) {
    $transaction_id = $_GET['id'];

    try {
        // Begin transaction
        $conn->beginTransaction();

        // Fetch the transaction
        $transaction_query = $conn->prepare("SELECT * FROM transactions WHERE t_ID = :transaction_id");
        $transaction_query->execute(['transaction_id' => $transaction_id]);
        $transaction = $transaction_query->fetch(PDO::FETCH_ASSOC);

        if ($transaction) {
            $tenant_id = $transaction['t_Recipient'];
            $amount = $transaction['t_Amount'];
            $type = $transaction['t_Type'];

            // Delete the transaction
            $delete_query = $conn->prepare("DELETE FROM transactions WHERE t_ID = :transaction_id");
            $delete_query->execute(['transaction_id' => $transaction_id]);

            // Update tenant's balance
            if ($type === 'income') {
                $balance_change = -$amount;
            } elseif ($type === 'expense') {
                $balance_change = $amount;
            }

            $update_balance_query = $conn->prepare("UPDATE user SET u_Balance = u_Balance + :balance_change WHERE u_ID = :tenant_id");
            $update_balance_query->execute([
                'balance_change' => $balance_change,
                'tenant_id' => $tenant_id
            ]);
        }

        // Commit transaction
        $conn->commit();

        // Redirect back to the owner dashboard
        header("Location: owner_dashboard.php");
        exit;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        echo "<p style='color:red;'>Failed to delete transaction: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo '<a href="owner_dashboard.php" class="btn">Back to Dashboard</a>';
        exit;
    }
} else {
    header("Location: owner_dashboard.php");
    exit;
}
?>
