<?php
// login.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Fetch the user
    $query = $conn->prepare("SELECT * FROM user WHERE u_Username = :username");
    $query->execute(['username' => $username]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    // Verify credentials
    if ($user && password_verify($password, $user['u_Password'])) {
        $_SESSION['user_role'] = $user['u_Role'];
        $_SESSION['user_id'] = $user['u_ID'];

        // Redirect based on role
        $redirectPage = $user['u_Role'] === 'tenant' ? 'tenant_dashboard.php' : 'owner_dashboard.php';
        header("Location: $redirectPage");
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
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
            background-color: #007BFF;
            border: none;
            border-radius: 4px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        label {
            display: inline-block;
            width: 100px;
            margin-bottom: 10px;
        }

        input {
            padding: 5px;
            margin-bottom: 10px;
            width: 200px;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Login</h1>
    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required><br>
        
        <label>Password:</label>
        <input type="password" name="password" required><br>
        
        <input type="submit" value="Login" class="btn">
    </form>
    <a href="signup.php" class="btn">Create New Account</a>
</body>
</html>
