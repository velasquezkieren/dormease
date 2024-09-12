<?php
$con = mysqli_connect("localhost", "root", "", "dormease");

if (!$con) {
    die("<script>alert(`Connection to the database failed`);</script>");
    exit();
}
