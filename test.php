<?php
if (isset($_POST['submit'])) {
    $text = $_POST['user_input'];
    echo $text;
}
?>

<form method="post" action="">
    <input type="text" name="user_input">
    <input type="submit" name="submit" value="Submit"> <!-- Added name attribute -->
</form>