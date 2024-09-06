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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="scripts/jquery-3.7.1.min.js"></script>
    <link rel="icon" type="image/x-icon" href="./img/favicon.png">

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
    <header><?php include('navbar.php'); ?></header>
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
        <?php
        include('footer.php'); //footer 
        ?>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
</body>

</html>