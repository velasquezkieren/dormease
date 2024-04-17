<?php

// title bar page
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    if ($page === 'login') {
        $title = 'Login | DormEase';
    } elseif ($page === 'feed') {
        $title = 'Feed | DormEase';
    } elseif ($page == 'about') {
        $title = 'About | DormEase';
    } elseif ($page == 'find') {
        $title = 'Find a Home | DormEase';
    } elseif ($page == 'signup') {
        $title = 'Sign Up | DormEase';
    } elseif ($page == 'profile') {
        $title = 'Profile | DormEase';
    } else {
        $title = 'DormEase';
    }
} else {
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
    <link rel="icon" type="image/x-icon" href="./img/favicon.png">
</head>

<body>
    <header>
        <?php
        include('navbar.php'); //navbar
        ?>
    </header>
    <!-- navigation links -->
    <?php
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
        if ($page === 'login') {
            include('./pages/login.php');
        } elseif ($page === 'feed') {
            include('./pages/feed.php');
        } elseif ($page == 'about') {
            include('./pages/about.php');
        } elseif ($page == 'find') {
            include('./pages/find.php');
        } elseif ($page == 'signup') {
            include('./pages/signup.php');
        } elseif ($page == 'profile') {
            include('./pages/profile.php');
        } else {
            include('./pages/homepage.php');
        }
    } else {
        include('./pages/homepage.php');
    }
    ?>
    <footer>
        <?php include('footer.php'); ?>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>

</html>