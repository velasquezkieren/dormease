<?php
// tenant_dashboard.php
session_start();
include 'config.php';

checkUserRole('tenant');

$user_id = $_SESSION['user_id'];

// Fetch tenant's transactions
$transactions_query = $conn->prepare("SELECT * FROM transactions WHERE t_Recipient = :user_id ORDER BY t_Date DESC");
$transactions_query->execute(['user_id' => $user_id]);
$transactions = $transactions_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch tenant's current balance
$balance_query = $conn->prepare("SELECT u_Balance FROM user WHERE u_ID = :user_id");
$balance_query->execute(['user_id' => $user_id]);
$balance = $balance_query->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tenant Dashboard</title>
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
    <h1>Tenant Ledger and Expenses</h1>
    <p><strong>Your current balance:</strong> $<?= htmlspecialchars(number_format($balance, 2)) ?></p>

    <h2>Your Transactions</h2>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
            <th>Type</th>
            <th>Date</th>
        </tr>
        <?php if (count($transactions) > 0): ?>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
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
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No transactions found.</td>
            </tr>
        <?php endif; ?>
    </table>
    <a href="logout.php" class="btn">Logout</a>
</body>
</html>
