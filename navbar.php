<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
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
        <nav class="navbar navbar-expand-lg fixed-top" style="height:80px;">
            <div class="container-fluid">
                <a class="navbar-brand me-auto" href=".?page=index"><img src="img/logo-no-background.png" height="50" width="auto"></a>
                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="offcanvasNavbarLabel"><a href=".?page=index"><img src="img/logo-no-background.png" height="50" width="auto"></a></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                            <li class="nav-item">
                                <a class="nav-link mx-lg-2 <?php echo ($title == 'Home | DormEase') ? 'active' : ''; ?>" aria-current="page" href=".?page=index">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mx-lg-2 <?php echo ($title == 'About Us | DormEase') ? 'active' : ''; ?>" href=".?page=about">About Us</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mx-lg-2 <?php echo ($title == 'Team | DormEase') ? 'active' : ''; ?>" href=".?page=team">Team</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mx-lg-2 <?php echo ($title == 'Help | DormEase') ? 'active' : ''; ?>" href=".?page=help">Help</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mx-lg-2 <?php echo ($title == 'List | DormEase') ? 'active' : ''; ?>" href=".?page=list">List Your Property!</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <a href=".?page=login" class="login-button">Login</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </nav>
    </header>
    <main></main>
    <footer>
        <!-- place footer here -->
    </footer>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>

</html>