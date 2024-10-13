<?php
include('config.php');
session_start();

if (isset($_SESSION['u_Email'])) {
    $firstname = $_SESSION['u_FName']; // Get the user's first name from session
    $account_type = $_SESSION['u_Account_Type']; // Get the user's account type from session
    $u_ID = $_SESSION['u_ID']; // Get the user's ID from session
}

// Logout functionality
if (isset($_GET['page']) && $_GET['page'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: login?logout-success");
    exit(); // Ensure to stop script execution after redirection
}
?>

<!-- nav bar -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand me-auto" href="<?php echo isset($_SESSION['u_Email']) ? 'profile' : 'home'; ?>">
            <img src="assets/logo_img/logo-c.svg" height="50" width="auto">
        </a>
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavbarLabel"><a href="home"><img src="assets/logo_img/logo-c.svg" height="50" width="auto"></a></h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                    <li class="nav-item">
                        <a class="nav-link mx-lg-2 <?php echo ($title == 'DormEase') ? 'active' : ''; ?>" href="home">Home</a>
                    </li>
                    <?php
                    // Show "Find a Home" or "List Your Property!" based on account type
                    if (!isset($_SESSION['u_Account_Type'])) {
                        echo '<li class="nav-item">
                            <a class="nav-link mx-lg-2 ' . (($title == 'About | DormEase') ? 'active' : '') . '" href="about">About</a>
                            </li>';
                        echo '<li class="nav-item">
                            <a class="nav-link mx-lg-2 ' . (($title == 'Find a Home | DormEase') ? 'active' : '') . '" href="find">Find a Home</a>
                            </li>';
                        echo '<li class="nav-item">
                            <a class="nav-link mx-lg-2 ' . (($title == 'List Property | DormEase') ? 'active' : '') . '" href="listing">List Your Property!</a>
                            </li>';
                    } elseif (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 1) {
                        echo '<li class="nav-item">
                            <a class="nav-link mx-lg-2 ' . (($title == 'Find a Home | DormEase') ? 'active' : '') . '" href="find">Find a Home</a>
                            </li>';
                    } elseif (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 0) {
                        echo '<li class="nav-item">
                            <a class="nav-link mx-lg-2 ' . (($title == 'Find a Home | DormEase') ? 'active' : '') . '" href="find">Find a Dorm</a>
                            </li>';
                    }

                    // Show "Inbox" only if logged in
                    if (isset($_SESSION['u_Email'])) {
                        echo '<li class="nav-item">
                            <a class="nav-link mx-lg-2 ' . (($title == 'Inbox | DormEase') ? 'active' : '') . '" href="inbox">Inbox</a>
                            </li>';
                    }

                    if (isset($_SESSION['u_Email'])) {
                        echo '<li class="nav-item">
                                    <a class="nav-link mx-lg-2 ' . (($title == "Profile | DormEase") ? "active" : "") . '" href="profile?u_ID=' . $u_ID . '">' . $firstname . '</a>
                                  </li>'; //link to profile if signed in
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?php
        if (isset($_SESSION['u_Email'])) {
            echo '<a class="login-button" href="logout">Logout</a>'; //logout button if signed in
        }
        if (!isset($_SESSION['u_Email'])) {
            echo '<a href="login" class="login-button">Login</a>'; //login button to sign in
        }
        ?>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>