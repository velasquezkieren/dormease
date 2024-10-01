<?php
// edit_transaction.php
session_start();
include 'config.php';

checkUserRole('owner');

if (isset($_GET['id'])) {
    $transaction_id = $_GET['id'];

    // Fetch the transaction
    $transaction_query = $conn->prepare("SELECT * FROM transactions WHERE t_ID = :transaction_id");
    $transaction_query->execute(['transaction_id' => $transaction_id]);
    $transaction = $transaction_query->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        echo "<p style='color:red;'>Transaction not found.</p>";
        echo '<a href="owner_dashboard.php" class="btn">Back to Dashboard</a>';
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tenant_id = $_POST['tenant_id'];
        $description = $_POST['description'];
        $amount = $_POST['amount'];
        $type = $_POST['type'];

        // Validate input
        if (empty($tenant_id) || empty($description) || empty($amount) || empty($type)) {
            echo "<p style='color:red;'>All fields are required.</p>";
        } else {
            try {
                // Begin transaction
                $conn->beginTransaction();

                // Calculate balance difference
                if ($transaction['t_Type'] === 'income') {
                    $original_balance_change = $transaction['t_Amount'];
                } else {
                    $original_balance_change = -$transaction['t_Amount'];
                }

                if ($type === 'income') {
                    $new_balance_change = $amount;
                } else {
                    $new_balance_change = -$amount;
                }

                $difference = $new_balance_change - $original_balance_change;

                // Update transaction
                $update_query = $conn->prepare("UPDATE transactions 
                                                SET t_Recipient = :tenant_id, t_Description = :description, t_Amount = :amount, t_Type = :type 
                                                WHERE t_ID = :transaction_id");
                $update_query->execute([
                    'tenant_id' => $tenant_id,
                    'description' => $description,
                    'amount' => $amount,
                    'type' => $type,
                    'transaction_id' => $transaction_id
                ]);

                // Update tenant's balance
                $update_balance_query = $conn->prepare("UPDATE user SET u_Balance = u_Balance + :difference WHERE u_ID = :tenant_id");
                $update_balance_query->execute([
                    'difference' => $difference,
                    'tenant_id' => $tenant_id
                ]);

                // Commit transaction
                $conn->commit();

                // Redirect back to the owner dashboard
                header("Location: owner_dashboard.php");
                exit;
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollBack();
                echo "<p style='color:red;'>Failed to update transaction: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
} else {
    header("Location: owner_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Transaction</title>
    <style>
        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 5px;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
            color: white;
            background-color: #007BFF;
            border: none;
            border-radius: 4px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        label {
            display: inline-block;
            width: 120px;
            margin-bottom: 10px;
        }

        input, select {
            padding: 5px;
            margin-bottom: 10px;
            width: 200px;
        }
    </style>
</head>
<body>
    <h1>Edit Transaction</h1>
    <form method="POST">
        <label for="tenant_id">Select Tenant:</label>
        <select name="tenant_id" required>
            <option value="">--Select Tenant--</option>
            <?php
            // Fetch tenants to populate the dropdown
            $tenant_query = $conn->prepare("SELECT u_ID, u_Username FROM user WHERE u_Role = 'tenant'");
            $tenant_query->execute();
            $tenants = $tenant_query->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($tenants as $tenant) {
                $selected = ($tenant['u_ID'] == $transaction['t_Recipient']) ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($tenant['u_ID']) . "' $selected>" . htmlspecialchars($tenant['u_Username']) . "</option>";
            }
            ?>
        </select><br>

        <label for="description">Description:</label>
        <input type="text" name="description" value="<?= htmlspecialchars($transaction['t_Description']) ?>" required><br>

        <label for="amount">Amount:</label>
        <input type="number" step="0.01" name="amount" value="<?= htmlspecialchars($transaction['t_Amount']) ?>" required><br>

        <label for="type">Type:</label>
        <select name="type" required>
            <option value="">--Select Type--</option>
            <option value="income" <?= ($transaction['t_Type'] === 'income') ? 'selected' : '' ?>>Income</option>
            <option value="expense" <?= ($transaction['t_Type'] === 'expense') ? 'selected' : '' ?>>Expense</option>
        </select><br>

        <input type="submit" value="Update Transaction" class="btn">
    </form>
    <a href="owner_dashboard.php" class="btn">Back to Dashboard</a>
</body>
</html>
