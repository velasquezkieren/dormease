<?php
if (isset($_SESSION['email']) && (isset($_SESSION['password']))) {
    header("location:?page=feed");
    exit();
}

?>
<style>
    .main {
        position: relative;
        overflow: hidden;
        /* Ensures that the blur effect does not overflow */
    }

    .bg-blur {
        position: absolute;
        height: 60rem;
        width: 100%;
        background: url('./img/bg-home.jpg') no-repeat;
        background-size: cover;
        background-position: 0% 100%;
        filter: blur(0.5vh);
        /* Adjust the blur amount as needed */
        z-index: -1;
        /* Move the blurred background behind other content */
    }
</style>

<section class="main py-5">
    <div class="bg-blur"></div>
    <div class="container py-5">
        <div class="row py-5">
            <div class="col-lg-7 pt-5 text-left">
                <h1 class="text-light mb-4 fw-bold pt-5" style="font-size:3.5rem;">Student Living<br>Made Simple</h1>
                <p class="text-light mb-4 lead">Simplifying Student Living.</p>
                <a class="btn btn-success" href="index.php?page=signup">Sign Up</a>
                <a class="btn btn-danger" href="index.php?page=login">Login</a>
            </div>
        </div>
    </div>
</section>

<section class="featured">
    <div class="container py-5">

    </div>
</section>