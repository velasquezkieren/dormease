<?php
ob_start();
$page = isset($_GET['page']) ? $_GET['page'] : 'index';
switch ($page) {
    case 'about':
        $title = 'About | DormEase';
        break;
    case 'find':
        $title = 'Find a Home | DormEase';
        break;
    case 'listing':
        $title = 'List Property | DormEase';
        break;
    case 'profile':
        $title = 'Profile | DormEase';
        break;
    case 'login':
        $title = 'Login | DormEase';
        break;
    case 'signup':
        $title = 'Sign Up | DormEase';
        break;
    case 'property':
        $title = 'Dorm | DormEase';
        break;
    case 'my-listings':
        $title = 'Listings | DormEase';
        break;
    case 'ledger':
        $title = 'Ledger | DormEase';
        break;
    case 'add-ledger':
        $title = 'Ledger | DormEase';
        break;
    case 'statement':
        $title = 'Statement | DormEase';
        break;
    case 'edit-ledger':
        $title = 'Ledger | DormEase';
        break;
    case 'messages':
        $title = 'Messages | DormEase';
        break;
    default:
        $title = 'DormEase';
}
?>

<!doctype html>
<html lang="en">

<head>
    <title><? echo $title; ?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS &JS -->
    <link rel="stylesheet" href="./assets/node_modules/bootstrap/dist/css/bootstrap.min.css">
    <script src="./assets/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="./assets/node_modules/bootstrap-icons/font/bootstrap-icons.min.css">

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="./assets/leaflet.css">
    <script src="./assets/leaflet.js"></script>

    <!-- JQuery -->
    <script src="./scripts/jquery-3.7.1.min.js"></script>

    <!-- Custom Icon -->
    <link rel="icon" type="image/x-icon" href="./assets/favicon.svg">

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
        case 'about':
            include('./pages/about.php');
            break;
        case 'find':
            include('./pages/find.php');
            break;
        case 'listing':
            include('./pages/listing.php');
            break;
        case 'profile':
            include('./pages/profile.php');
            break;
        case 'login':
            include('./pages/login.php');
            break;
        case 'signup':
            include('./pages/signup.php');
            break;
        case 'property':
            include('./pages/property.php');
            break;
        case 'my-listings':
            include('./pages/my-listings.php');
            break;
        case 'ledger':
            include('./pages/ledger.php');
            break;
        case 'add-ledger':
            include('./pages/add-ledger.php');
            break;
        case 'statement':
            include('./pages/statement.php');
            break;
        case 'edit-ledger':
            include('./pages/edit-ledger.php');
            break;
        case 'delete-ledger':
            include('./pages/delete-ledger.php');
            break;
        case 'application':
            include('./pages/application.php');
            break;
        case 'scheduled-visits':
            include('./pages/scheduled-visits.php');
            break;
        case 'visit-schedules':
            include('./pages/visit-schedules.php');
            break;
        case 'messages':
            include('./pages/messages.php');
            break;
        case 'add-room':
            include('./pages/add-room.php');
            break;
        case 'tenants':
            include('./pages/tenants.php');
            break;
        case 'accept_tenant':
            include('./pages/accept_tenant.php');
            break;
        case 'reject_tenant':
            include('./pages/reject_tenant.php');
            break;
        case 'evict_tenant':
            include('./pages/evict_tenant.php');
            break;
        case 'bookings':
            include('./pages/bookings.php');
            break;
        case 'delete-room':
            include('./pages/delete-room.php');
            break;
        default:
            include('./pages/landing.php');
            break;
    }

    if ($page !== 'messages') {
        echo '<footer class="mt-5 text-center text-lg-start bg-body-tertiary text-muted">';
        include('footer.php'); // Footer for all pages except 'messages'
        echo '</footer>';
    }
    ?>

</body>

</html>