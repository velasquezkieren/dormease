<?php
// signup.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validate inputs
    if (empty($username) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {
        // Check if username already exists
        $check_query = $conn->prepare("SELECT * FROM user WHERE u_Username = :username");
        $check_query->execute(['username' => $username]);
        if ($check_query->rowCount() > 0) {
            $error = "Username already exists.";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO user (u_Username, u_Password, u_Role, u_Balance) VALUES (:username, :password, :role, 0)");
            $stmt->execute([
                'username' => $username,
                'password' => $hashed_password,
                'role' => $role
            ]);

            $success = "User created successfully! You can now <a href='login.php'>login</a>.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
    <style>
        /* Add your styles here */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 5px;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
            color: white;
            background-color: #28a745;
            border: none;
            border-radius: 4px;
        }

        .btn:hover {
            background-color: #218838;
        }

        label {
            display: inline-block;
            width: 100px;
            margin-bottom: 10px;
        }

        input, select {
            padding: 5px;
            margin-bottom: 10px;
            width: 200px;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }
    </style>
</head>
<body>
    <h1>Sign Up</h1>
    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <p class="success"><?= $success ?></p>
    <?php endif; ?>
    <form method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" placeholder="Username" required><br>
        
        <label for="password">Password:</label>
        <input type="password" name="password" placeholder="Password" required><br>
        
        <label for="role">Role:</label>
        <select name="role" required>
            <option value="">--Select Role--</option>
            <option value="owner">Owner</option>
            <option value="tenant">Tenant</option>
        </select><br>
        
        <input type="submit" value="Sign Up" class="btn">
    </form>
    <a href="login.php" class="btn">Back to Login</a>
</body>
</html>
