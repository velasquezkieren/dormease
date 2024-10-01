<?php
// owner_dashboard.php
session_start();
include 'config.php';

checkUserRole('owner');

// Fetch all transactions
$transactions_query = $conn->prepare("
    SELECT transactions.*, user.u_Username 
    FROM transactions 
    JOIN user ON transactions.t_Recipient = user.u_ID
    ORDER BY transactions.t_Date DESC
");
$transactions_query->execute();
$transactions = $transactions_query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Owner Dashboard</title>
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

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
            text-align: left;
        }

        .income {
            color: green;
        }

        .expense {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Owner Ledger and Expenses Management</h1>
    <a href="add_transaction.php" class="btn">Add New Transaction</a>
    <a href="logout.php" class="btn">Logout</a>

    <h2>All Transactions</h2>
    <table>
        <tr>
            <th>Tenant Username</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Type</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        <?php if (count($transactions) > 0): ?>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?= htmlspecialchars($transaction['u_Username']) ?></td>
                    <td><?= htmlspecialchars($transaction['t_Description']) ?></td>
                    <td>
                        <?php 
                            if ($transaction['t_Type'] === 'expense') {
                                echo "- $" . htmlspecialchars(number_format($transaction['t_Amount'], 2));
                            } else {
                                echo "$" . htmlspecialchars(number_format($transaction['t_Amount'], 2));
                            }
                        ?>
                    </td>
                    <td class="<?= htmlspecialchars($transaction['t_Type']) ?>">
                        <?= ucfirst(htmlspecialchars($transaction['t_Type'])) ?>
                    </td>
                    <td><?= htmlspecialchars($transaction['t_Date']) ?></td>
                    <td>
                        <a href="edit_transaction.php?id=<?= htmlspecialchars($transaction['t_ID']) ?>" class="btn">Edit</a> | 
                        <a href="delete_transaction.php?id=<?= htmlspecialchars($transaction['t_ID']) ?>" class="btn" onclick="return confirm('Are you sure you want to delete this transaction?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No transactions found.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>
