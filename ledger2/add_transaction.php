<?php
// add_transaction.php
session_start();
include 'config.php';

checkUserRole('owner');

// Check if the form was submitted
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

            // Insert new transaction
            $insert_query = $conn->prepare("INSERT INTO transactions (t_Recipient, t_Description, t_Amount, t_Type, t_Date) 
                                            VALUES (:tenant_id, :description, :amount, :type, NOW())");
            $insert_query->execute([
                'tenant_id' => $tenant_id,
                'description' => $description,
                'amount' => $amount,
                'type' => $type
            ]);

            // Update tenant's balance
            if ($type === 'income') {
                $balance_change = $amount;
            } elseif ($type === 'expense') {
                $balance_change = -$amount;
            }

            $update_balance_query = $conn->prepare("UPDATE user SET u_Balance = u_Balance + :balance_change WHERE u_ID = :tenant_id");
            $update_balance_query->execute([
                'balance_change' => $balance_change,
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
            echo "<p style='color:red;'>Failed to add transaction: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Transaction</title>
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
    <h1>Add Transaction</h1>
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
                echo "<option value='" . htmlspecialchars($tenant['u_ID']) . "'>" . htmlspecialchars($tenant['u_Username']) . "</option>";
            }
            ?>
        </select><br>

        <label for="description">Description:</label>
        <input type="text" name="description" required><br>

        <label for="amount">Amount:</label>
        <input type="number" step="0.01" name="amount" required><br>

        <label for="type">Type:</label>
        <select name="type" required>
            <option value="">--Select Type--</option>
            <option value="income">Income</option>
            <option value="expense">Expense</option>
        </select><br>

        <input type="submit" value="Add Transaction" class="btn">
    </form>
    <a href="owner_dashboard.php" class="btn">Back to Dashboard</a>
</body>
</html>
