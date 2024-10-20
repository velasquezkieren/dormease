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
                        <a class="nav-link mx-lg-2 <?php echo ($title == 'DormEase') ? 'active' : ''; ?>" href="home">
                            Home
                        </a>
                    </li>
                    <?php
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

                    if (isset($_SESSION['u_Email'])) {
                        echo '<li class="nav-item">
                            <a class="nav-link mx-lg-2 ' . (($title == 'Messages | DormEase') ? 'active' : '') . '" href="messages">Messages</a>
                            </li>';
                    }

                    if (isset($_SESSION['u_Email'])) {
                        echo '<li class="nav-item">
                                    <a class="nav-link mx-lg-2 ' . (($title == "Profile | DormEase") ? "active" : "") . '" href="profile?u_ID=' . $u_ID . '">' . $firstname . '</a>
                                  </li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?php
        if (isset($_SESSION['u_Email'])) {
            echo '<a class="login-button" href="logout">Logout</a>';
        }
        if (!isset($_SESSION['u_Email'])) {
            echo '<a href="login" class="login-button">Login</a>';
        }
        ?>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<!-- Bottom navigation bar for mobile -->
<div class="mobile-nav d-block d-lg-none fixed-bottom bg-light">
    <div class="container d-flex justify-content-around py-2">
        <a href="home" class="text-center nav-link">
            <i class="bi bi-house-door-fill"></i>
            <small>Home</small>
        </a>

        <!-- Conditional links based on account type and session -->
        <?php
        // If user is not logged in, show 'Find a Home' and 'List Your Property'
        if (!isset($_SESSION['u_Account_Type'])) {
            echo '<a href="find" class="text-center nav-link">
                    <i class="bi bi-search"></i>
                    <small>Find a Home</small>
                  </a>';
            echo '<a href="listing" class="text-center nav-link">
                    <i class="bi bi-building"></i>
                    <small>List Property</small>
                  </a>';
        } elseif (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 1) {
            // If user is a tenant
            echo '<a href="find" class="text-center nav-link">
                    <i class="bi bi-search"></i>
                    <small>Find a Home</small>
                  </a>';
        } elseif (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 0) {
            // If user is a dorm owner
            echo '<a href="find" class="text-center nav-link">
                    <i class="bi bi-search"></i>
                    <small>Find a Dorm</small>
                  </a>';
        }

        // Show 'Messages' link only if logged in
        if (isset($_SESSION['u_Email'])) {
            echo '<a href="messages" class="text-center nav-link">
                    <i class="bi bi-chat-square-text-fill"></i>
                    <small>Messages</small>
                  </a>';
        }

        // Show 'Profile' if logged in, otherwise show 'Login'
        if (isset($_SESSION['u_Email'])) {
            echo '<a href="profile?u_ID=' . $u_ID . '" class="text-center nav-link">
                    <i class="bi bi-person-fill"></i>
                    <small>Profile</small>
                  </a>';
        } else {
            echo '<a href="login" class="text-center nav-link">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <small>Login</small>
                  </a>';
        }
        ?>
    </div>
</div>

<!-- CSS -->
<style>
    .mobile-nav {
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        z-index: 1030;
        /* Ensure it's above other content */
    }

    .mobile-nav a {
        text-decoration: none;
        color: #883D1A;
    }

    .mobile-nav i {
        font-size: 1.5rem;
    }

    .mobile-nav small {
        display: block;
        font-size: 0.75rem;
    }

    @media (max-width: 992px) {
        .navbar {
            display: none;
        }
    }
</style>