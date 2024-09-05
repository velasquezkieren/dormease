<?php
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
        $title = 'Details | DormEase';
        break;
    default:
        $title = 'DormEase';
}

?>

<!doctype html>
<html lang="en">

<head>
    <title><?php echo $title; ?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="scripts/leaflet.js"></script>
    <script src="scripts/jquery-3.7.1.min.js"></script>
    <link rel="icon" type="image/x-icon" href="./img/favicon.png">
    <link rel="stylesheet" href="css/leaflet.css">

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
        include('navbar.php'); //navbar
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
        default:
            include('./pages/homepage.php');
            break;
    }
    ?>
    <footer class="mt-5 text-center text-lg-start bg-body-tertiary text-muted">
        <?php include('footer.php'); //footer 
        ?>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>

</html>