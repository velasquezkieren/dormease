<?php
include('config.php');
session_start();

if (isset($_SESSION['email'])) {
    $firstname = $_SESSION['firstname']; // Get the user's first name from session
}

if (isset($_GET['page'])) {
    $page = $_GET['page'];
    if ($page == 'logout') {
        session_unset();
        session_destroy();
        header("location:index.php");
    }
}

?>

<head>
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

<!-- nav bar -->
<header>
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
                            <?php
                            if (isset($_SESSION['email'])) {
                                echo '<a class="nav-link mx-lg-2" aria-current="page" href="index.php?page=feed">Home</a>';
                            } else {
                                echo '<a class="nav-link mx-lg-2" aria-current="page" href="index.php?page=index">Home</a>';
                            }
                            ?>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mx-lg-2" href="index.php?page=about">About Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mx-lg-2" href="index.php?page=help">Help</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mx-lg-2" href="index.php?page=list">List Your Property!</a>
                        </li>
                        <?php
                        if (isset($_SESSION['email'])) {
                            echo '<li class="nav-item">
                                    <a class="nav-link mx-lg-2" href="index.php?page=profile">' . $firstname . '</a>
                                  </li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>

            <?php
            if (isset($_SESSION['email'])) {
                echo '<a class="login-button" href="index.php?page=logout">Logout</a>';
            }
            if (!isset($_SESSION['email'])) {
                echo '<a href="index.php?page=login" class="login-button">Login</a>';
            }
            ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
</header>