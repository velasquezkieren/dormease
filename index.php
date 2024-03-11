<?php
// Define a mapping of page titles
$pageTitles = array(
    'homepage' => 'DormEase',
    'login' => 'Login | DormEase',
    'about' => 'About Us | DormEase',
    'help' => 'Help | DormEase',
    'list' => 'List | DormEase',
    'team' => 'Team | DormEase'
);

// Set default title
$title = "DormEase";

// Get the requested page name
$page = isset($_GET['page']) ? ($_GET['page'] == 'index' ? 'homepage' : $_GET['page']) : 'homepage';

// Check if the requested page exists in the mapping
if (array_key_exists($page, $pageTitles)) {
    // If the page exists, set its title
    $title = $pageTitles[$page];
}
?>

<!doctype html>
<html lang="en">

<head>
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
    <title><?php echo $title; ?></title>

</head>

<body>
    <header>
        <?php include('navbar.php'); ?>
    </header>
    <main>
        <div class="container-fluid">
            <?php
            $folder = isset($_GET['folder']) ? $_GET['folder'] : 'pages/';
            $page = isset($_GET['page']) ? ($_GET['page'] == 'index' ? 'homepage' : $_GET['page']) : 'homepage';
            require_once($folder . $page . '.php');
            ?>
        </div>

    </main>
    <footer>
        <!-- place footer here -->
    </footer>
</body>

</html>