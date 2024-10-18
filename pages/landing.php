<?php
// Fetch featured dormitories
$query = "SELECT * FROM dormitory WHERE d_Featured = 1 AND d_RegistrationStatus = 1"; // Added d_RegistrationStatus check
$result = mysqli_query($con, $query);
?>

<style>
    .main {
        position: relative;
        overflow: hidden;
    }

    .bg-blur {
        position: absolute;
        height: 60rem;
        width: 100%;
        background: url('./img/bg-home.jpg') no-repeat;
        background-size: cover;
        background-position: 0% 100%;
        filter: blur(0.5vh);
        z-index: -1;
    }
</style>

<div class="min-vh-100">
    <!-- Hero section -->
    <section class="jumbotron main">
        <div class="bg-blur"></div>
        <div class="container py-5">
            <div class="row py-5">
                <div class="col-lg-7 pt-5 text-left">
                    <h1 class="text-light mb-4 fw-bold pt-5" style="font-size:3.5rem;">Student Living<br>Made Simple</h1>
                    <p class="text-light mb-4 lead">Simplifying Student Living.</p>
                    <?php if (!isset($_SESSION['u_Email'])): ?>
                        <a class="btn btn-outline-light btn-md" href="signup">Sign Up</a>
                        <a class="btn btn-light btn-lmd" href="login">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured -->
    <section class="featured">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 text-center m-auto pt-5 pb-5">
                    <h1>Featured Dormitories</h1>
                    <h6>Comfortable and affordable living for you</h6>
                </div>
            </div>
            <div class="row">
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($dorm = mysqli_fetch_assoc($result)) {
                        // Fetch the owner's name
                        $owner_ID = mysqli_real_escape_string($con, $dorm['d_Owner']);
                        $owner_query = mysqli_query($con, "SELECT u_FName, u_MName, u_LName, u_Picture FROM user WHERE u_ID = '$owner_ID'");
                        $owner_data = mysqli_fetch_assoc($owner_query);
                        $owner_name = $owner_data ? htmlspecialchars($owner_data['u_FName'] . ' ' . $owner_data['u_MName'] . ' ' . $owner_data['u_LName']) : 'Unknown';
                        $owner_pic = htmlspecialchars($owner_data['u_Picture']);

                        // Get the image names
                        $images = explode(',', $dorm['d_PicName']);
                        $first_image = $images[0];

                        // Limit the description to 100 characters
                        $description = substr($dorm['d_Description'], 0, 100);
                        if (strlen($dorm['d_Description']) > 100) {
                            $description .= '...';
                        }
                ?>
                        <!-- Dorm Cards -->
                        <div class="col-lg-3 col-md-6 col-12 mb-2">
                            <a href="property?d_ID=<?= urlencode($dorm['d_ID']); ?>" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm">

                                    <!-- Carousel -->
                                    <div id="carousel-<?= $dorm['d_ID']; ?>" class="carousel slide card-img-container" data-bs-ride="carousel">
                                        <div class="carousel-inner">
                                            <?php foreach ($images as $index => $image): ?>
                                                <div class="carousel-item <?= $index === 0 ? 'active' : ''; ?>">
                                                    <img src="upload/<?= htmlspecialchars($dorm['d_ID'] . '/' . $image); ?>" class="d-block w-100 img-fluid" loading="lazy" alt="<?= htmlspecialchars($dorm['d_Name']); ?>" style="height: 200px; object-fit: cover;">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?= $dorm['d_ID']; ?>" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?= $dorm['d_ID']; ?>" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>
                                    <!-- End Carousel -->

                                    <!-- Dorm Details -->
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?= htmlspecialchars($dorm['d_Name']); ?></h5>
                                        <p class="card-text text-truncate" style="max-height: 3.6em; overflow: hidden;"><?= htmlspecialchars($description); ?></p>
                                        <p class="card-text"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($dorm['d_Street']) . ', ' . htmlspecialchars($dorm['d_City']); ?></p>
                                        <p class="card-text"><img src="user_avatar/<?= htmlspecialchars($owner_pic); ?>" class="img-fluid rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;"> <?= htmlspecialchars($owner_name); ?></p>
                                        <span class="btn btn-dark mt-auto">View Details</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                <?php
                    }
                } else {
                    echo '<div class="alert alert-secondary text-center mx-auto p-5" role="alert">No featured dormitories available at the moment.</div>';
                }
                ?>
            </div>
        </div>
    </section>
</div>