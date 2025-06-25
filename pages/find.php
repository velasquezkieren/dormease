<?php
$is_logged_in = isset($_SESSION['u_Email']);
// Fetch the maximum price
$max_price_query = mysqli_query($con, "SELECT MAX(d_Price) AS max_price FROM dormitory WHERE d_RegistrationStatus = 1 AND d_Availability = 1");
$max_price_data = mysqli_fetch_assoc($max_price_query);
$max_price = $max_price_data ? $max_price_data['max_price'] : 10000; // Default to 10,000 if no data


if (isset($_POST['search']) || isset($_POST['sort'])) {
    $search_query = mysqli_real_escape_string($con, $_POST['search']);
    $sort_order = isset($_POST['sort']) ? mysqli_real_escape_string($con, $_POST['sort']) : '';
    $selected_amenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];

    // Sorting logic
    $order_by = '';
    if ($sort_order == '1') {
        $order_by = 'ORDER BY d_Name ASC';
    } elseif ($sort_order == '2') {
        $order_by = 'ORDER BY d_Name DESC';
    } elseif ($sort_order == '3') {
        $order_by = 'ORDER BY d_Price ASC';
    } elseif ($sort_order == '4') {
        $order_by = 'ORDER BY d_Price DESC';
    }

    $price_min = isset($_POST['price_min']) ? (int)$_POST['price_min'] : 0;
    $price_max = isset($_POST['price_max']) ? (int)$_POST['price_max'] : $max_price;

    // Amenities filtering
    $amenities_condition = '';
    if (!empty($selected_amenities)) {
        $amenities_sql_parts = array_map(function ($amenity) use ($con) {
            return "d_Amenities LIKE '%" . mysqli_real_escape_string($con, $amenity) . "%'";
        }, $selected_amenities);
        $amenities_condition = ' AND (' . implode(' OR ', $amenities_sql_parts) . ')';
    }

    $sql = "SELECT * FROM dormitory 
    WHERE (d_Name LIKE '%$search_query%' OR d_Street LIKE '%$search_query%' OR d_City LIKE '%$search_query%') 
    AND d_RegistrationStatus = 1 
    AND d_Availability = 1 
    AND d_Price BETWEEN $price_min AND $price_max 
    $amenities_condition
    $order_by";

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
                            <h5 class="card-title text-truncate"><?= htmlspecialchars($dorm['d_Name']); ?></h5>
                            <p class="card-text text-truncate" style="max-height: 3.6em; overflow: hidden;"><?= htmlspecialchars($description); ?></p>
                            <p class="card-text text-truncate"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($dorm['d_Street']) . ', ' . htmlspecialchars($dorm['d_City']); ?></p>
                            <p class="card-text text-truncate"><img src="user_avatar/<?= htmlspecialchars($owner_pic); ?>" class="img-fluid rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;"> <?= htmlspecialchars($owner_name); ?></p>
                            <p class="card-text"><strong>Price:</strong> ₱<?= number_format($dorm['d_Price'], 2); ?></p>
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
    <div class="container min-vh-100 mt-5 mt-0 mt-sm-5">
        <!-- Search Bar and Filters Button (side by side in mobile view) -->
        <div class="row pt-5 pt-0 pt-sm-5">
            <div class="col-12 d-flex justify-content-between">
                <div class="input-group mb-3 w-100">
                    <input class="form-control" list="datalistOptions" id="searchInput" placeholder="Type to search...">
                </div>

                <!-- Off-canvas Filters Button (visible only on mobile) -->
                <button class="btn btn-dark d-lg-none mb-3 ms-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offCanvasFilters" aria-controls="offCanvasFilters">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
                        <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Off-canvas Filters (for mobile) -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offCanvasFilters" aria-labelledby="offCanvasFiltersLabel">
            <div class="offcanvas-header">
                <h5 id="offCanvasFiltersLabel">Filters</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <!-- Sort By -->
                <div class="mb-3">
                    <label for="offCanvasSortSelect" class="form-label">Sort By</label>
                    <select class="form-select" id="offCanvasSortSelect">
                        <option value="" selected disabled>Choose...</option>
                        <option value="1">Name - A to Z</option>
                        <option value="2">Name - Z to A</option>
                        <option value="3">Price - Lowest to Highest</option>
                        <option value="4">Price - Highest to Lowest</option>
                    </select>
                </div>

                <!-- offcanvas Price Range -->
                <div class="mb-3">
                    <label class="form-label">Price Range (₱)</label>
                    <div class="d-flex flex-column flex-md-row align-items-md-center">
                        <input type="number" id="priceRangeMin" class="form-control me-md-2 mb-2 mb-md-0" min="0" max="<?= $max_price; ?>" step="100" value="0" placeholder="Min">
                        <span class="d-none d-md-inline">to</span>
                        <input type="number" id="priceRangeMax" class="form-control ms-md-2" min="0" max="<?= $max_price; ?>" step="100" value="<?= $max_price; ?>" placeholder="Max">
                    </div>
                    <small id="priceRangeValue" class="text-muted mt-1 d-block">₱0 - ₱<?= number_format($max_price); ?></small>
                </div>

                <!-- Amenities Filter (Mobile Version) -->
                <div class="mb-3">
                    <label class="form-label">Amenities</label>
                    <div class="form-check">
                        <input class="form-check-input amenity-checkbox" type="checkbox" value="Wi-Fi" id="amenityWiFi">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-wifi" viewBox="0 0 16 16">
                            <path d="M15.384 6.115a.485.485 0 0 0-.047-.736A12.44 12.44 0 0 0 8 3C5.259 3 2.723 3.882.663 5.379a.485.485 0 0 0-.048.736.52.52 0 0 0 .668.05A11.45 11.45 0 0 1 8 4c2.507 0 4.827.802 6.716 2.164.205.148.49.13.668-.049" />
                            <path d="M13.229 8.271a.482.482 0 0 0-.063-.745A9.46 9.46 0 0 0 8 6c-1.905 0-3.68.56-5.166 1.526a.48.48 0 0 0-.063.745.525.525 0 0 0 .652.065A8.46 8.46 0 0 1 8 7a8.46 8.46 0 0 1 4.576 1.336c.206.132.48.108.653-.065m-2.183 2.183c.226-.226.185-.605-.1-.75A6.5 6.5 0 0 0 8 9c-1.06 0-2.062.254-2.946.704-.285.145-.326.524-.1.75l.015.015c.16.16.407.19.611.09A5.5 5.5 0 0 1 8 10c.868 0 1.69.201 2.42.56.203.1.45.07.61-.091zM9.06 12.44c.196-.196.198-.52-.04-.66A2 2 0 0 0 8 11.5a2 2 0 0 0-1.02.28c-.238.14-.236.464-.04.66l.706.706a.5.5 0 0 0 .707 0l.707-.707z" />
                        </svg>
                        <label class="form-check-label" for="amenityWiFi">Wi-Fi</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input amenity-checkbox" type="checkbox" value="Parking" id="amenityParking">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-car-front-fill" viewBox="0 0 16 16">
                            <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679q.05.242.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.8.8 0 0 0 .381-.404l.792-1.848ZM3 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2m10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2M6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2zM2.906 5.189a.51.51 0 0 0 .497.731c.91-.073 3.35-.17 4.597-.17s3.688.097 4.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 11.691 3H4.309a.5.5 0 0 0-.447.276L2.906 5.19Z" />
                        </svg>
                        <label class="form-check-label" for="amenityParking">Parking</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input amenity-checkbox" type="checkbox" value="Furniture" id="amenityFurniture">
                        <svg height="16" width="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path d="M2 2H0V14H2V12H14V14H16V9C16 7.34315 14.6569 6 13 6H6C6 4.89543 5.10457 4 4 4H2V2Z" fill="#000000"></path>
                            </g>
                        </svg>
                        <label class="form-check-label" for="amenityFurniture">Furniture</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input amenity-checkbox" type="checkbox" value="Pet-Friendly" id="amenityPetFriendly">
                        <svg height="16" width="16" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 48.839 48.839" xml:space="preserve" fill="#000000">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <g>
                                    <path style="fill:#030104;" d="M39.041,36.843c2.054,3.234,3.022,4.951,3.022,6.742c0,3.537-2.627,5.252-6.166,5.252 c-1.56,0-2.567-0.002-5.112-1.326c0,0-1.649-1.509-5.508-1.354c-3.895-0.154-5.545,1.373-5.545,1.373 c-2.545,1.323-3.516,1.309-5.074,1.309c-3.539,0-6.168-1.713-6.168-5.252c0-1.791,0.971-3.506,3.024-6.742 c0,0,3.881-6.445,7.244-9.477c2.43-2.188,5.973-2.18,5.973-2.18h1.093v-0.001c0,0,3.698-0.009,5.976,2.181 C35.059,30.51,39.041,36.844,39.041,36.843z M16.631,20.878c3.7,0,6.699-4.674,6.699-10.439S20.331,0,16.631,0 S9.932,4.674,9.932,10.439S12.931,20.878,16.631,20.878z M10.211,30.988c2.727-1.259,3.349-5.723,1.388-9.971 s-5.761-6.672-8.488-5.414s-3.348,5.723-1.388,9.971C3.684,29.822,7.484,32.245,10.211,30.988z M32.206,20.878 c3.7,0,6.7-4.674,6.7-10.439S35.906,0,32.206,0s-6.699,4.674-6.699,10.439C25.507,16.204,28.506,20.878,32.206,20.878z M45.727,15.602c-2.728-1.259-6.527,1.165-8.488,5.414s-1.339,8.713,1.389,9.972c2.728,1.258,6.527-1.166,8.488-5.414 S48.455,16.861,45.727,15.602z"></path>
                                </g>
                            </g>
                        </svg>
                        <label class="form-check-label" for="amenityPetFriendly">Pet-Friendly</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input amenity-checkbox" type="checkbox" value="Curfew" id="amenityCurfew">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16">
                            <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z" />
                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0" />
                        </svg>
                        <label class="form-check-label" for="amenityCurfew">Curfew</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input amenity-checkbox" type="checkbox" value="Water Bill Included" id="amenityWater">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-droplet-fill" viewBox="0 0 16 16">
                            <path d="M8 16a6 6 0 0 0 6-6c0-1.655-1.122-2.904-2.432-4.362C10.254 4.176 8.75 2.503 8 0c0 0-6 5.686-6 10a6 6 0 0 0 6 6M6.646 4.646l.708.708c-.29.29-1.128 1.311-1.907 2.87l-.894-.448c.82-1.641 1.717-2.753 2.093-3.13" />
                        </svg>
                        <label class="form-check-label" for="amenityWater">Water Bill Included</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input amenity-checkbox" type="checkbox" value="Electric Bill Included" id="amenityElectric">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lightning-charge-fill" viewBox="0 0 16 16">
                            <path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z" />
                        </svg>
                        <label class="form-check-label" for="amenityElectric">Electric Bill Included</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input amenity-checkbox" type="checkbox" value="Other" id="amenityOther">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                            <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" />
                        </svg>
                        <label class="form-check-label" for="amenityOther">Other</label>
                    </div>
                </div>

                <button class="btn btn-dark w-100" onclick="loadDorms()">Apply Filters</button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Sidebar Filters (visible only on desktop) -->
            <div class="col-lg-3 col-md-4 col-12 mb-4 d-none d-lg-block">
                <div class="border p-3 rounded shadow-sm">
                    <h5 class="fw-bold mb-3">Filters</h5>

                    <!-- Sort By -->
                    <div class="mb-3">
                        <label for="sortSelect" class="form-label">Sort By</label>
                        <select class="form-select" id="desktopSortSelect">
                            <option value="" selected disabled>Choose...</option>
                            <option value="1">Name - A to Z</option>
                            <option value="2">Name - Z to A</option>
                            <option value="3">Price - Lowest to Highest</option>
                            <option value="4">Price - Highest to Lowest</option>
                        </select>
                    </div>

                    <!-- desktop Price Range -->
                    <div class="mb-3">
                        <label class="form-label">Price Range (₱)</label>
                        <div class="d-flex flex-column flex-md-row align-items-md-center">
                            <input type="number" id="desktopPriceRangeMin" class="form-control me-md-2 mb-2 mb-md-0" min="0" max="<?= $max_price; ?>" step="100" value="0" placeholder="Min">
                            <span class="d-none d-md-inline">to</span>
                            <input type="number" id="desktopPriceRangeMax" class="form-control ms-md-2" min="0" max="<?= $max_price; ?>" step="100" value="<?= $max_price; ?>" placeholder="Max">
                        </div>
                        <small id="desktopPriceRangeValue" class="text-muted mt-1 d-block">₱0 - ₱<?= number_format($max_price); ?></small>
                    </div>


                    <!-- Amenities Filter -->
                    <div class="mb-3">
                        <label class="form-label">Amenities</label>
                        <div class="form-check">
                            <input class="form-check-input amenity-checkbox" type="checkbox" value="Wi-Fi" id="amenityWiFi">
                            <label class="form-check-label" for="amenityWiFi">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-wifi" viewBox="0 0 16 16">
                                    <path d="M15.384 6.115a.485.485 0 0 0-.047-.736A12.44 12.44 0 0 0 8 3C5.259 3 2.723 3.882.663 5.379a.485.485 0 0 0-.048.736.52.52 0 0 0 .668.05A11.45 11.45 0 0 1 8 4c2.507 0 4.827.802 6.716 2.164.205.148.49.13.668-.049" />
                                    <path d="M13.229 8.271a.482.482 0 0 0-.063-.745A9.46 9.46 0 0 0 8 6c-1.905 0-3.68.56-5.166 1.526a.48.48 0 0 0-.063.745.525.525 0 0 0 .652.065A8.46 8.46 0 0 1 8 7a8.46 8.46 0 0 1 4.576 1.336c.206.132.48.108.653-.065m-2.183 2.183c.226-.226.185-.605-.1-.75A6.5 6.5 0 0 0 8 9c-1.06 0-2.062.254-2.946.704-.285.145-.326.524-.1.75l.015.015c.16.16.407.19.611.09A5.5 5.5 0 0 1 8 10c.868 0 1.69.201 2.42.56.203.1.45.07.61-.091zM9.06 12.44c.196-.196.198-.52-.04-.66A2 2 0 0 0 8 11.5a2 2 0 0 0-1.02.28c-.238.14-.236.464-.04.66l.706.706a.5.5 0 0 0 .707 0l.707-.707z" />
                                </svg>
                                Wi-Fi
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input amenity-checkbox" type="checkbox" value="Parking" id="amenityParking">
                            <label class="form-check-label" for="amenityParking">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-car-front-fill" viewBox="0 0 16 16">
                                    <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679q.05.242.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.8.8 0 0 0 .381-.404l.792-1.848ZM3 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2m10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2M6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2zM2.906 5.189a.51.51 0 0 0 .497.731c.91-.073 3.35-.17 4.597-.17s3.688.097 4.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 11.691 3H4.309a.5.5 0 0 0-.447.276L2.906 5.19Z" />
                                </svg>
                                Parking
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input amenity-checkbox" type="checkbox" value="Furniture" id="amenityFurniture">
                            <label class="form-check-label" for="amenityFurniture">
                                <svg height="16" width="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path d="M2 2H0V14H2V12H14V14H16V9C16 7.34315 14.6569 6 13 6H6C6 4.89543 5.10457 4 4 4H2V2Z" fill="#000000"></path>
                                    </g>
                                </svg>
                                Furniture
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input amenity-checkbox" type="checkbox" value="Pet-Friendly" id="amenityPetFriendly">
                            <label class="form-check-label" for="amenityPetFriendly">
                                <svg height="16" width="16" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 48.839 48.839" xml:space="preserve" fill="#000000">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <g>
                                            <path style="fill:#030104;" d="M39.041,36.843c2.054,3.234,3.022,4.951,3.022,6.742c0,3.537-2.627,5.252-6.166,5.252 c-1.56,0-2.567-0.002-5.112-1.326c0,0-1.649-1.509-5.508-1.354c-3.895-0.154-5.545,1.373-5.545,1.373 c-2.545,1.323-3.516,1.309-5.074,1.309c-3.539,0-6.168-1.713-6.168-5.252c0-1.791,0.971-3.506,3.024-6.742 c0,0,3.881-6.445,7.244-9.477c2.43-2.188,5.973-2.18,5.973-2.18h1.093v-0.001c0,0,3.698-0.009,5.976,2.181 C35.059,30.51,39.041,36.844,39.041,36.843z M16.631,20.878c3.7,0,6.699-4.674,6.699-10.439S20.331,0,16.631,0 S9.932,4.674,9.932,10.439S12.931,20.878,16.631,20.878z M10.211,30.988c2.727-1.259,3.349-5.723,1.388-9.971 s-5.761-6.672-8.488-5.414s-3.348,5.723-1.388,9.971C3.684,29.822,7.484,32.245,10.211,30.988z M32.206,20.878 c3.7,0,6.7-4.674,6.7-10.439S35.906,0,32.206,0s-6.699,4.674-6.699,10.439C25.507,16.204,28.506,20.878,32.206,20.878z M45.727,15.602c-2.728-1.259-6.527,1.165-8.488,5.414s-1.339,8.713,1.389,9.972c2.728,1.258,6.527-1.166,8.488-5.414 S48.455,16.861,45.727,15.602z"></path>
                                        </g>
                                    </g>
                                </svg>
                                Pet-Friendly
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input amenity-checkbox" type="checkbox" value="Curfew" id="amenityCurfew">
                            <label class="form-check-label" for="amenityCurfew">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16">
                                    <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z" />
                                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0" />
                                </svg>
                                Curfew
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input amenity-checkbox" type="checkbox" value="Water Bill Included" id="amenityWater">
                            <label class="form-check-label" for="amenityWater">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-droplet-fill" viewBox="0 0 16 16">
                                    <path d="M8 16a6 6 0 0 0 6-6c0-1.655-1.122-2.904-2.432-4.362C10.254 4.176 8.75 2.503 8 0c0 0-6 5.686-6 10a6 6 0 0 0 6 6M6.646 4.646l.708.708c-.29.29-1.128 1.311-1.907 2.87l-.894-.448c.82-1.641 1.717-2.753 2.093-3.13" />
                                </svg>
                                Water Bill Included
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input amenity-checkbox" type="checkbox" value="Electric Bill Included" id="amenityElectric">
                            <label class="form-check-label" for="amenityElectric">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lightning-charge-fill" viewBox="0 0 16 16">
                                    <path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z" />
                                </svg>
                                Electric Bill Included
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input amenity-checkbox" type="checkbox" value="Other" id="amenityOther">
                            <label class="form-check-label" for="amenityOther">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                    <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" />
                                </svg>
                                Other
                            </label>
                        </div>

                    </div>
                    <button class="btn btn-dark w-100" onclick="loadDorms()">Apply Filters</button>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-lg-9 col-md-8 col-12">
                <div class="row" id="dormsContainer">
                    <!-- Dormitory listings will be dynamically loaded here -->
                </div>
            </div>
        </div>
    </div>

<?php
}
?>

<script>
    $(document).ready(function() {
        let timeout = null;

        // Function to sync the price range display text
        function syncPriceInputs() {
            const minPrice = window.innerWidth <= 991 ? ($('#priceRangeMin').val() || 0) : ($('#desktopPriceRangeMin').val() || 0);
            const maxPrice = window.innerWidth <= 991 ? ($('#priceRangeMax').val() || <?= $max_price ?>) : ($('#desktopPriceRangeMax').val() || <?= $max_price ?>);

            $('#priceRangeValue').text(`₱${parseInt(minPrice).toLocaleString()} - ₱${parseInt(maxPrice).toLocaleString()}`);
            $('#desktopPriceRangeValue').text(`₱${parseInt(minPrice).toLocaleString()} - ₱${parseInt(maxPrice).toLocaleString()}`);

        }

        // Function to load the dorms based on the selected filters
        function loadDorms() {
            const search_query = $('#searchInput').val();

            // For desktop sort
            const sort_order = $('#desktopSortSelect').val();
            // For mobile sort (off-canvas)
            const offCanvasSortOrder = $('#offCanvasSortSelect').val();
            // Determine the final sort order
            const finalSortOrder = sort_order || offCanvasSortOrder;

            // Get price range values based on screen width
            const minPrice = window.innerWidth <= 991 ? ($('#priceRangeMin').val() || 0) : ($('#desktopPriceRangeMin').val() || 0);
            const maxPrice = window.innerWidth <= 991 ? ($('#priceRangeMax').val() || <?= $max_price ?>) : ($('#desktopPriceRangeMax').val() || <?= $max_price ?>);

            // Collect selected amenities
            const selectedAmenities = [];
            $('.amenity-checkbox:checked').each(function() {
                selectedAmenities.push($(this).val());
            });

            // Make the AJAX request to fetch dorms based on the filters
            $.ajax({
                type: 'POST',
                url: window.location.href,
                data: {
                    search: search_query,
                    sort: finalSortOrder,
                    price_min: minPrice,
                    price_max: maxPrice,
                    amenities: selectedAmenities
                },
                success: function(response) {
                    $('#dormsContainer').html(response);
                }
            });
        }

        // Sync price range display and trigger loading dorms on input change
        $('#priceRangeMin, #priceRangeMax, #desktopPriceRangeMin, #desktopPriceRangeMax, #desktopSortSelect, #searchInput').on('input', function() {
            syncPriceInputs();
            clearTimeout(timeout);
            timeout = setTimeout(loadDorms, 300);
        });

        // Sync price range display and trigger loading dorms for mobile sort
        $('#priceRangeMin, #priceRangeMax, #offCanvasSortSelect').on('change', function() {
            clearTimeout(timeout);
            timeout = setTimeout(loadDorms, 300);
        });

        // Handle changes to amenities checkboxes
        $('.amenity-checkbox').on('change', function() {
            loadDorms();
        });

        // Initial synchronization of price inputs and loading dorms
        syncPriceInputs();
        loadDorms();
    });
</script>