<?php
include('config.php');
session_start();

if (isset($_SESSION['email'])) {
    $firstname = $_SESSION['firstname']; // Get the user's first name from session
    $account_type = $_SESSION['account_type']; // Get the user's account type from session
}

// logout = destroy session
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    if ($page == 'logout') {
        session_unset();
        session_destroy();
        header("location:?page=login&logout-success");
    }
}

?>

<!-- nav bar -->
<nav class="navbar navbar-expand-lg fixed-top" style="height:80px;">
    <div class="container-fluid">
        <a class="navbar-brand me-auto" href="index.php?page=index"><img src="img/logo-no-background.png" height="50" width="auto"></a>
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavbarLabel"><a href=".?page=index"><img src="img/logo-no-background.png" height="50" width="auto"></a></h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                    <li class="nav-item">
                        <a class="nav-link mx-lg-2 <?php echo ($title == 'DormEase') ? 'active' : ''; ?>" href="index.php?page=index">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mx-lg-2 <?php echo ($title == 'About | DormEase') ? 'active' : ''; ?>" href="index.php?page=about">About Us</a>
                    </li>
                    <?php
                    // account
                    if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 1) {
                        echo '<li class="nav-item">
                            <a class="nav-link mx-lg-2 ' . (($title == 'Find a Home | DormEase') ? 'active' : '') . '" href="index.php?page=find">Find a Home</a>
                            </li>';
                    } elseif (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 0) {

                        echo '<li class="nav-item">
                            <a class="nav-link mx-lg-2 ' . (($title == 'List Property | DormEase') ? 'active' : '') . '" href="index.php?page=list">List Your Property!</a>
                            </li>';
                    } else {
                        echo '<li class="nav-item">
                            <a class="nav-link mx-lg-2 ' . (($title == 'Find a Home | DormEase') ? 'active' : '') . '" href="index.php?page=find">Find a Home</a>
                            </li>';
                        echo '<li class="nav-item">
                            <a class="nav-link mx-lg-2 ' . (($title == 'List Property | DormEase') ? 'active' : '') . '" href="index.php?page=list">List Your Property!</a>
                            </li>';
                    }
                    ?>
                    <?php
                    if (isset($_SESSION['email'])) {
                        echo '<li class="nav-item">
                                    <a class="nav-link mx-lg-2 ' . (($title == "Profile | DormEase") ? "active" : "") . '" href="index.php?page=profile">' . $firstname . '</a>
                                  </li>'; //link to profile if signed in
                    }
                    ?>
                </ul>
            </div>
        </div>

        <?php
        if (isset($_SESSION['email'])) {
            echo '<a class="login-button" href="index.php?page=logout">Log out</a>'; //logout button if signed in
        }
        if (!isset($_SESSION['email'])) {
            echo '<a href="index.php?page=login" class="login-button">Login</a>'; //login button to sign in
        }
        ?>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>