<?php
include('config.php');
if (!isset($_SESSION['email']) && (!isset($_SESSION['password']))) {
    header("location:?page=index");
    die();
}
?>

<h1>hi</h1>