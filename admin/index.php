<?php
ob_start();
$page = isset($_GET['page']) ? $_GET['page'] : 'index';
switch ($page) {
    case 'login':
        $title = 'Login | DormEase';
        break;
    case 'signup':
        $title = 'Sign Up | DormEase';
        break;
    default:
        $title = 'DormEase';
}
?>

<h1>Hello world</h1>


<?php
// navigation links
$page = isset($_GET['page']) ? $_GET['page'] : 'index';

switch ($page) {
    case 'login':
        include('./admin/pages/login.php');
        break;
    case 'signup':
        include('./admin/pages/signup.php');
        break;
    default:
        include('./admin/pages/login.php');
        break;
}
?>