<?php
ob_start();
$page = isset($_GET['page']) ? $_GET['page'] : 'index';
switch ($page) {
    case 'login':
        $title = 'Login | DormEase';
        break;
    case 'dashboard':
        $title = 'Dashboard | DormEase';
        break;
    case 'signup':
        $title = 'Sign Up | DormEase';
        break;
    case 'owners-list':
        $title = 'Owner | DormEase';
        break;
    case 'tenants-list':
        $title = 'Tenants | DormEase';
        break;
    case 'active-dorm':
        $title = 'Active | DormEase';
        break;
    case 'inactive-dorm':
        $title = 'Inactive | DormEase';
        break;
    case 'settings':
        $title = 'Settings | DormEase';
        break;
    default:
        $title = 'Admin | DormEase';
}
?>

<!doctype html>
<html lang="en">

<head>
    <title><? echo $title; ?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS &JS -->
    <link rel="stylesheet" href="../assets/node_modules/bootstrap/dist/css/bootstrap.min.css">
    <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../assets/node_modules/bootstrap-icons/font/bootstrap-icons.min.css">

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="../assets/leaflet.css">
    <script src="../assets/leaflet.js"></script>

    <!-- JQuery -->
    <script src="../scripts/jquery-3.7.1.min.js"></script>

    <!-- Custom Icon -->
    <link rel="stylesheet" href="../css/nav-style.css">
    <link rel="icon" type="image/x-icon" href="../assets/favicon.svg">

    <!-- style for navbar -->
    <style>
        .navbar {
            background-color: #FAF9F6;
        }

        .login-button {
            background-color: #883D1A;
            color: #FFFFFF;
            font-size: 14px;
            padding: 8px 20px;
            text-decoration: none;
            border-radius: 50px;
            transition: 0.3s background-color;
        }

        .login-button:hover {
            background-color: #2A160C;
            box-shadow: 0 0 10px rgba(42, 22, 12, 0.5);
        }

        .navbar-toggler {
            border: none;
            font-size: 1.25rem;
        }

        .navbar-toggler:focus,
        .btn-close:focus {
            box-shadow: none;
            outline: none;
        }

        .nav-link {
            font-weight: 500;
            color: #883D1A;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #2A160C;
        }
    </style>
</head>

<body>
    <header>
        <?php
        include('navbar.php');
        ?>
    </header>
    <?php
    // navigation links
    $page = isset($_GET['page']) ? $_GET['page'] : 'index';

    switch ($page) {
        case 'login':
            include('../admin/pages/login.php');
            break;
        case 'dashboard':
            include('../admin/pages/dashboard.php');
            break;
        case 'signup':
            include('../admin/pages/signup.php');
            break;
        case 'owners-list':
            include('../admin/pages/owners-list.php');
            break;
        case 'tenants-list':
            include('../admin/pages/tenants-list.php');
            break;
        case 'active-dorm':
            include('../admin/pages/active-dorm.php');
            break;
        case 'inactive-dorm':
            include('../admin/pages/inactive-dorm.php');
            break;
        case 'edit-dorm':
            include('../admin/pages/edit-dorm.php');
            break;
        case 'delete-dorm':
            include('../admin/pages/delete-dorm.php');
            break;
        case 'settings':
            include('../admin/pages/settings.php');
            break;
        case 'inactive-owners':
            include('../admin/pages/inactive-owners.php');
            break;
        case 'user-approval':
            include('../admin/pages/user-approval.php');
            break;
        case 'inactive-room':
            include('../admin/pages/inactive-room.php');
            break;
        case 'active-room':
            include('../admin/pages/active-room.php');
            break;
        case 'edit-room':
            include('../admin/pages/edit-room.php');
            break;
        case 'delete-room':
            include('../admin/pages/delete-room.php');
            break;
        default:
            include('../admin/pages/login.php');
            break;
    }
    ?>

</body>

</html>