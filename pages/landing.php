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

<!-- hero section -->
<section class="jumbotron main">
    <div class="bg-blur"></div>
    <div class="container py-5">
        <div class="row py-5">
            <div class="col-lg-7 pt-5 text-left">
                <h1 class="text-light mb-4 fw-bold pt-5" style="font-size:3.5rem;">Student Living<br>Made Simple</h1>
                <p class="text-light mb-4 lead">Simplifying Student Living.</p>
                <?php
                if (!isset($_SESSION['u_Email'])) {
                ?>
                    <a class="btn btn-outline-light btn-md" href="signup">Sign Up</a>
                    <a class="btn btn-light btn-lmd" href="login">Login</a>
                <?php } ?>
            </div>
        </div>
    </div>
</section>

<!-- featured -->
<section class="featured">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 text-center m-auto pt-5 pb-5">
                <h1>Featured Dormitories</h1>
                <h6>Comfortable and affordable living for you</h6>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 border-0 card mb-2">
                <img src="img/sample.jpg" class="card-img-top" alt="...">
                <div class="card-body">
                    <h5 class="card-title">Cabanatuan</h5>
                    <h6 class="card-text"><i class="bi bi-geo-alt-fill"></i>FX49+J2M, Lungsod ng Cabanatuan, Nueva Ecija</h6>
                    <p class="card-text">Starts at<br>$1,000</p>
                    <i class="bi bi-star-fill card-text">5.0 Rating</i><br>
                    <a href="#" class="btn btn-primary">View Property</a>
                </div>
            </div>
            <div class="col-lg-3 border-0 card mb-2">
                <img src="img/sample.jpg" class="card-img-top" alt="...">
                <div class="card-body">
                    <h5 class="card-title">Cabanatuan</h5>
                    <h6 class="card-text"><i class="bi bi-geo-alt-fill"></i>FX49+J2M, Lungsod ng Cabanatuan, Nueva Ecija</h6>
                    <p class="card-text">Starts at<br>$1,000</p>
                    <i class="bi bi-star-fill card-text">5.0 Rating</i><br>
                    <a href="#" class="btn btn-primary">View Property</a>
                </div>
            </div>
            <div class="col-lg-3 border-0 card mb-2">
                <img src="img/sample.jpg" class="card-img-top" alt="...">
                <div class="card-body">
                    <h5 class="card-title">Cabanatuan</h5>
                    <h6 class="card-text"><i class="bi bi-geo-alt-fill"></i>FX49+J2M, Lungsod ng Cabanatuan, Nueva Ecija</h6>
                    <p class="card-text">Starts at<br>$1,000</p>
                    <i class="bi bi-star-fill card-text">5.0 Rating</i><br>
                    <a href="#" class="btn btn-primary">View Property</a>
                </div>
            </div>
            <div class="col-lg-3 border-0 card mb-2">
                <img src="img/sample.jpg" class="card-img-top" alt="...">
                <div class="card-body">
                    <h5 class="card-title">Cabanatuan</h5>
                    <h6 class="card-text"><i class="bi bi-geo-alt-fill"></i>FX49+J2M, Lungsod ng Cabanatuan, Nueva Ecija</h6>
                    <p class="card-text">Starts at<br>$1,000</p>
                    <i class="bi bi-star-fill card-text">5.0 Rating</i><br>
                    <a href="#" class="btn btn-primary">View Property</a>
                </div>
            </div>
        </div>
    </div>
</section>