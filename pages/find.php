<?php
$is_logged_in = isset($_SESSION['u_Email']);

if (!$is_logged_in) {
?>
    <section class="p-3 p-md-4 p-xl-5">
        <div class="container p-xl-5" style="margin-top: 100px;">
            <div class="row">
                <div class="col-12 col-md-8 offset-md-2 col-xl-6 offset-xl-1">
                    <h1 class="fw-bold">Find your dorm</h1>
                    <p class="text-left lead">Searching for the ideal dormitory space? Look no further! Share your preferences and requirements with us, and we'll connect you with available dorms that match your criteria. Say goodbye to the hassle of hunting for the perfect dorm – let us streamline the process for you!</p>
                    <a href="login" class="btn btn-dark btn-lg">Get Started</a>
                </div>
            </div>
        </div>
    </section>
    <?php
} else {
    // Fetch all dormitories from the database
    $sql = "SELECT * FROM dormitory";
    $dorms_query = mysqli_query($con, $sql);

    if (mysqli_num_rows($dorms_query) > 0):
    ?>
        <div class="container">
            <div class="row m-auto">
                <div class="col">
                    <div class="input-group mt-5 pt-5 p-4">
                        <input class="form-control" list="datalistOptions" id="exampleDataList" placeholder="Type to search...">
                        <datalist id="datalistOptions">
                            <option value="Cabanatuan">
                            <option value="Sta. Rosa">
                            <option value="Sumacab">
                        </datalist>
                    </div>
                </div>
                <div class="col">
                    <div class="input-group mt-5 pt-5">
                        <select class="form-select" aria-label="Default select example">
                            <option value="Sort By" selected disabled>Sort By</option>
                            <option value="1">Price - Low to High</option>
                            <option value="2">Price - High to Low</option>
                            <option value="3">Rating - Low to High</option>
                            <option value="4">Rating - High to Low</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <?php while ($dorm = mysqli_fetch_assoc($dorms_query)): ?>
                    <?php
                    // Fetch the owner's name
                    $owner_ID = mysqli_real_escape_string($con, $dorm['d_Owner']);
                    $owner_query = mysqli_query($con, "SELECT u_FName, u_LName FROM user WHERE u_ID = '$owner_ID'");
                    $owner_data = mysqli_fetch_assoc($owner_query);
                    $owner_name = $owner_data ? htmlspecialchars($owner_data['u_FName'] . ' ' . $owner_data['u_LName']) : 'Unknown';

                    // Get the image names and use the first image for the card
                    $images = explode(',', $dorm['d_PicName']);
                    $first_image = $images[0];

                    // Limit the description to 100 characters
                    $description = substr($dorm['d_Description'], 0, 100);
                    if (strlen($dorm['d_Description']) > 100) {
                        $description .= '...';
                    }
                    ?>
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
                        <div class="card h-100 border-1">
                            <a href="property?d_ID=<?= urlencode($dorm['d_ID']); ?>">
                                <div class="card-img-container">
                                    <img src="upload/<?= htmlspecialchars($first_image); ?>" class="card-img-top" alt="<?= htmlspecialchars($dorm['d_Name']); ?>">
                                </div>
                            </a>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($dorm['d_Name']); ?></h5>
                                <p class="card-text text-truncate" style="max-height: 3.6em; overflow: hidden;"><?= htmlspecialchars($description); ?></p>
                                <p class="card-text"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($dorm['d_Street']) . ', ' . htmlspecialchars($dorm['d_City']); ?></p>
                                <p class="card-text"><strong>Owner:</strong> <?= htmlspecialchars($owner_name); ?></p>
                                <a href="property?d_ID=<?= urlencode($dorm['d_ID']); ?>" class="btn btn-dark mt-auto">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php
    else:
    ?>
        <div class="container">
            <div class="alert alert-secondary text-center mx-auto p-5" role="alert">
                No listings available at the moment
            </div>
        </div>
<?php endif;
}
?>