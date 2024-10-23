<?php
$is_logged_in = isset($_SESSION['u_Email']);

// Handle AJAX requests for search and sort
if (isset($_POST['search']) || isset($_POST['sort'])) {
    $search_query = mysqli_real_escape_string($con, $_POST['search']);
    $sort_order = mysqli_real_escape_string($con, $_POST['sort']);

    // Sort the query based on the sort_order
    $order_by = '';
    if ($sort_order == '1') {
        $order_by = 'ORDER BY d_Name ASC';
    } elseif ($sort_order == '2') {
        $order_by = 'ORDER BY d_Name DESC';
    }

    $sql = "SELECT * FROM dormitory WHERE (d_Name LIKE '%$search_query%' OR d_Street LIKE '%$search_query%' OR d_City LIKE '%$search_query%') AND d_RegistrationStatus = 1 $order_by";

    $dorms_query = mysqli_query($con, $sql);

    if (mysqli_num_rows($dorms_query) > 0) {
        while ($dorm = mysqli_fetch_assoc($dorms_query)) {
            // Fetch the owner's name
            $owner_ID = mysqli_real_escape_string($con, $dorm['d_Owner']);
            $owner_query = mysqli_query($con, "SELECT u_FName, u_MName, u_LName, u_Picture, u_ContactNumber FROM user WHERE u_ID = '$owner_ID'");
            $owner_data = mysqli_fetch_assoc($owner_query);
            $owner_name = $owner_data ? htmlspecialchars($owner_data['u_FName'] . ' ' . $owner_data['u_MName'] . ' ' . $owner_data['u_LName']) : 'Unknown';
            $owner_pic = htmlspecialchars($owner_data['u_Picture']);

            // Get the image names and use the first image for the card
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
        echo '<div class="alert alert-secondary text-center mx-auto p-5" role="alert">No listings available for your search</div>';
    }
    exit();
}

if (!$is_logged_in) {
    ?>
    <section class="p-3 p-md-4 p-xl-5 min-vh-100">
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
    // Initial fetch of all dormitories
?>
    <div class="container min-vh-100">
        <div class="row m-auto">
            <div class="col">
                <div class="input-group mt-md-5 p-2 pt-md-5">
                    <input class="form-control" list="datalistOptions" id="searchInput" placeholder="Type to search...">
                    <datalist id="datalistOptions">
                        <option value="Cabanatuan">
                        <option value="Sta. Rosa">
                        <option value="Sumacab">
                    </datalist>
                </div>
            </div>
            <div class="col">
                <div class="input-group mt-md-5 p-2 pt-md-5">
                    <select class="form-select" id="sortSelect" aria-label="Default select example">
                        <option value="" selected disabled>Sort By</option>
                        <option value="1">Name - A to Z</option>
                        <option value="2">Name - Z to A</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row mt-5" id="dormsContainer">
            <!-- Dormitory listings will be loaded here via AJAX -->
        </div>
    </div>
<?php
}
?>

<script>
    $(document).ready(function() {
        let timeout = null; // Variable for debounce

        function loadDorms() {
            var search_query = $('#searchInput').val();
            var sort_order = $('#sortSelect').val();

            $.ajax({
                type: 'POST',
                url: window.location.href, // Loads the file itself
                data: {
                    search: search_query,
                    sort: sort_order
                },
                success: function(response) {
                    $('#dormsContainer').html(response);
                }
            });
        }

        // Debounce search input
        $('#searchInput').on('keyup', function() {
            clearTimeout(timeout);
            timeout = setTimeout(loadDorms, 300); // Load after 300ms
        });

        // Load dorms when sort option changes
        $('#sortSelect').on('change', loadDorms);

        // Load all dorms on page load
        loadDorms();
    });
</script>