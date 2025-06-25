<?php
// Redirect students away from this page if they are logged in as students
if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 1) {
    header('Location: home');
    exit();
}

// Handle form submission when the user clicks the submit button
if (isset($_POST['submit'])) {
    // Sanitize input data to prevent SQL injection
    $d_ID = uniqid('d_');  // Generate a unique ID for the dormitory
    $d_Name = ucwords($_POST['d_Name']);
    $d_Street = ucwords($_POST['d_Street']);
    $d_City = ucwords($_POST['cityName']);
    $d_ZIPCode = $_POST['d_ZIPCode'];
    $d_Province = ucwords($_POST['provinceName']);
    $d_Region = ucwords($_POST['regionName']);
    $d_Description = $_POST['d_Description'];
    $d_Availability = '1';  // Default availability status
    $d_Price = $_POST['d_Price'];
    $d_Gender = $_POST['d_Gender'];
    $d_Amenities = isset($_POST['d_Amenities']) ? implode(',', $_POST['d_Amenities']) : '';
    $d_Owner = $_SESSION['u_ID'];  // Get the logged-in user ID from the session

    // Initialize array to store image names
    $imageNames = [];
    // Allowed image types
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    $maxFileSize = 5 * 1024 * 1024; // Maximum file size: 5MB

    // Check if the number of images uploaded is within the allowed range (3-5 images)
    if (count($_FILES['d_PicName']['name']) < 3 || count($_FILES['d_PicName']['name']) > 5) {
        echo '<script>alert("Please upload between 3 and 5 images.");</script>';
        exit();
    }

    // Begin a transaction
    $con->begin_transaction();

    try {
        // Create a directory for the dormitory using its unique ID
        $directoryPath = "upload/" . $d_ID . "/";
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0777, true);  // Create the directory with proper permissions
        }

        // Loop through each uploaded file
        foreach ($_FILES['d_PicName']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['d_PicName']['name'][$key];
            $file_tmp = $_FILES['d_PicName']['tmp_name'][$key];
            $file_type = $_FILES['d_PicName']['type'][$key];
            $file_size = $_FILES['d_PicName']['size'][$key];

            // Validate file type and size
            if (!in_array($file_type, $allowedTypes)) {
                throw new Exception("Invalid file type: " . htmlspecialchars($file_name));
            }
            if ($file_size > $maxFileSize) {
                throw new Exception("File size too large: " . htmlspecialchars($file_name));
            }

            // Generate a unique name for each image to avoid overwriting
            $uniqueFileName = uniqid() . '@dormease@' . $file_name;

            // Move the uploaded file to the new folder
            if (!move_uploaded_file($file_tmp, $directoryPath . $uniqueFileName)) {
                throw new Exception("Error uploading image: " . htmlspecialchars($file_name));
            }

            // Add the unique image name to the array
            $imageNames[] = $uniqueFileName;
        }

        // Convert array of image names to a comma-separated string
        $d_PicNames = implode(',', $imageNames);

        // Sanitize latitude and longitude
        $d_Latitude = $_POST['d_Latitude'];
        $d_Longitude = $_POST['d_Longitude'];

        // Prepare an SQL statement to insert dormitory details into the database
        $stmt = $con->prepare("INSERT INTO dormitory (d_ID, d_Name, d_Street, d_City, d_ZIPCode, d_Province, d_Region, d_Availability, d_Description, d_Owner, d_PicName, d_Latitude, d_Longitude, d_Price, d_Gender, d_Amenities) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters to the SQL statement
        $stmt->bind_param('ssssssssssssssss', $d_ID, $d_Name, $d_Street, $d_City, $d_ZIPCode, $d_Province, $d_Region, $d_Availability, $d_Description, $d_Owner, $d_PicNames, $d_Latitude, $d_Longitude, $d_Price, $d_Gender, $d_Amenities);

        // Execute the statement and check if it was successful
        if (!$stmt->execute()) {
            throw new Exception("Database insert failed: " . htmlspecialchars($stmt->error));
        }

        // Commit the transaction
        $con->commit();

        // Redirect to the profile page on success
        header("Location: my-listings");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction if there was an error
        $con->rollback();
        echo "<script>alert('Error: " . htmlspecialchars($e->getMessage()) . "');</script>";
    }
}
?>

<!-- HTML Section -->
<?php
if (!isset($_SESSION['u_Email'])) {
?>
    <!-- top section -->
    <div class="min-vh-100">
        <section class="p-3 p-md-4 p-xl-5">
            <div class="container p-xl-5" style="margin-top: 100px;">
                <div class="row">
                    <div class="col-12 col-md-8 offset-md-2 col-xl-6 offset-xl-1">
                        <h1 class="fw-bold">List your property</h1>
                        <p class="text-left lead">Listing your dormitory space online for rent has never been simpler. If you're eager to find tenants for your dormitory, just share some details about your space and yourself, and we'll connect you with genuine renters offering the best rates in the market.</p>
                        <a href="login" class="btn btn-dark btn-lg">Get Started</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php
} else {
?>
    <!-- form section -->
    <div class="min-vh-100">
        <section class="p-3 p-md-4 p-xl-5 bg-light-subtle">
            <div class="container" style="margin-top:100px;">
                <div class="row justify-content-center">
                    <div class="col-12 col-xxl-11" id="list-property">
                        <div class="card border-light-subtle shadow-sm">
                            <div class="row g-0">
                                <div class="col-12">
                                    <div class="card-body p-3 p-md-4 p-xl-5">
                                        <form action="" class="row g-3" enctype="multipart/form-data" method="post">
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="text" name="d_Name" class="form-control" id="floatingInput" placeholder="Dorm Name" required>
                                                    <label for="floatingInput">Dorm Name</label>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="text" name="d_Street" class="form-control" id="d_Street" placeholder="Street Address" required>
                                                    <label for="d_Street">Street Address</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-floating mb-3">
                                                    <select class="form-select" name="d_Region" id="d_Region" required>
                                                        <option value="" disabled selected>Select Region</option>
                                                        <input type="hidden" id="regionName" name="regionName">
                                                    </select>
                                                    <label for="d_Region">Region</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-floating mb-3">
                                                    <select class="form-select" name="d_Province" id="d_Province" required>
                                                        <option value="" disabled selected>Select Province</option>
                                                    </select>
                                                    <label for="d_Province">Province</label>
                                                    <input type="hidden" id="provinceName" name="provinceName">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-floating mb-3">
                                                    <select class="form-select" name="d_City" id="d_City" required>
                                                        <option value="" disabled selected>Select City/Municipality</option>
                                                    </select>
                                                    <label for="d_City">City/Municipality</label>
                                                    <input type="hidden" id="cityName" name="cityName">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="hidden" name="d_Latitude" id="latitude">
                                                    <input type="hidden" name="d_Longitude" id="longitude">
                                                    <div id="map" class="rounded" style="height: 450px; width:100%;"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-floating mb-3">
                                                    <input type="number" name="d_ZIPCode" class="form-control" id="d_ZIPCode" placeholder="ZIP Code" required>
                                                    <label for="d_ZIPCode">ZIP Code</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-floating mb-3">
                                                    <input type="number" name="d_Price" class="form-control" id="d_Price" placeholder="Price" required>
                                                    <label for="d_Price">Price</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-floating mb-3">
                                                    <select class="form-select" id="d_Gender" name="d_Gender" required>
                                                        <option value="" disabled selected>Select Gender Restriction</option>
                                                        <option value="">No Restriction</option>
                                                        <option value="1">Male Only</option>
                                                        <option value="0">Female Only</option>
                                                    </select>
                                                    <label for="d_Gender">Gender Restriction</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label for="d_Description" class="form-label">Property Description</label>
                                                <textarea class="form-control" name="d_Description" id="d_Description" rows="5" required></textarea>
                                            </div>

                                            <!-- Amenities -->
                                            <div class="col-12">
                                                <label for="d_Amenities" class="form-label">Amenities</label>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="d_Amenities[]" value="Wi-Fi" id="amenityWiFi">
                                                    <label class="form-check-label" for="amenityWiFi">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-wifi" viewBox="0 0 16 16">
                                                            <path d="M15.384 6.115a.485.485 0 0 0-.047-.736A12.44 12.44 0 0 0 8 3C5.259 3 2.723 3.882.663 5.379a.485.485 0 0 0-.048.736.52.52 0 0 0 .668.05A11.45 11.45 0 0 1 8 4c2.507 0 4.827.802 6.716 2.164.205.148.49.13.668-.049" />
                                                            <path d="M13.229 8.271a.482.482 0 0 0-.063-.745A9.46 9.46 0 0 0 8 6c-1.905 0-3.68.56-5.166 1.526a.48.48 0 0 0-.063.745.525.525 0 0 0 .652.065A8.46 8.46 0 0 1 8 7a8.46 8.46 0 0 1 4.576 1.336c.206.132.48.108.653-.065m-2.183 2.183c.226-.226.185-.605-.1-.75A6.5 6.5 0 0 0 8 9c-1.06 0-2.062.254-2.946.704-.285.145-.326.524-.1.75l.015.015c.16.16.407.19.611.09A5.5 5.5 0 0 1 8 10c.868 0 1.69.201 2.42.56.203.1.45.07.61-.091zM9.06 12.44c.196-.196.198-.52-.04-.66A2 2 0 0 0 8 11.5a2 2 0 0 0-1.02.28c-.238.14-.236.464-.04.66l.706.706a.5.5 0 0 0 .707 0l.707-.707z" />
                                                        </svg>
                                                        Wi-Fi
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="d_Amenities[]" value="Parking" id="amenityParking">
                                                    <label class="form-check-label" for="amenityParking">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-car-front-fill" viewBox="0 0 16 16">
                                                            <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679q.05.242.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.8.8 0 0 0 .381-.404l.792-1.848ZM3 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2m10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2M6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2zM2.906 5.189a.51.51 0 0 0 .497.731c.91-.073 3.35-.17 4.597-.17s3.688.097 4.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 11.691 3H4.309a.5.5 0 0 0-.447.276L2.906 5.19Z" />
                                                        </svg>
                                                        Parking
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="d_Amenities[]" value="Furniture" id="amenityFurniture">
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
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="d_Amenities[]" value="Pet-Friendly" id="amenityPetFriendly">
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
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="d_Amenities[]" value="Curfew" id="amenityCurfew">
                                                    <label class="form-check-label" for="amenityCurfew">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16">
                                                            <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z" />
                                                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0" />
                                                        </svg>
                                                        Curfew
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="d_Amenities[]" value="Water Bill Included" id="amenityWater">
                                                    <label class="form-check-label" for="amenityWater">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-droplet-fill" viewBox="0 0 16 16">
                                                            <path d="M8 16a6 6 0 0 0 6-6c0-1.655-1.122-2.904-2.432-4.362C10.254 4.176 8.75 2.503 8 0c0 0-6 5.686-6 10a6 6 0 0 0 6 6M6.646 4.646l.708.708c-.29.29-1.128 1.311-1.907 2.87l-.894-.448c.82-1.641 1.717-2.753 2.093-3.13" />
                                                        </svg>
                                                        Water Bill Included
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="d_Amenities[]" value="Electric Bill Included" id="amenityElectric">
                                                    <label class="form-check-label" for="amenityElectric">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lightning-charge-fill" viewBox="0 0 16 16">
                                                            <path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z" />
                                                        </svg>
                                                        Electric Bill Included
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="d_Amenities[]" value="Other" id="amenityOther">
                                                    <label class="form-check-label" for="amenityOther">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                                            <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" />
                                                        </svg>
                                                        Other
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <label for="d_PicName" class="form-label">Upload Images (3-5 images, maximum of 5 MB)</label>
                                                <input class="form-control" type="file" name="d_PicName[]" accept=".jpg, .jpeg, .png, .gif" multiple required onchange="previewImages(event)">
                                                <div id="image-preview" class="mt-2"></div>
                                            </div>
                                            <div class="col-12">
                                                <input type="submit" value="Submit" name="submit" class="btn btn-dark">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize Region Dropdown
            loadRegions();

            // Event listener for region change to load provinces
            $('#d_Region').change(function() {
                var regionName = $(this).find(":selected").text(); // Get the region name
                var regionId = $(this).val(); // Get the region geonameId
                if (regionId) {
                    loadProvinces(regionId, regionName);
                    // Set the region name to the hidden field for submission
                    $('#regionName').val(regionName);
                }
            });

            // Event listener for province change to load cities/municipalities
            $('#d_Province').change(function() {
                var provinceName = $(this).find(":selected").text(); // Get the province name
                var provinceId = $(this).val(); // Get the province geonameId
                if (provinceId) {
                    loadCities(provinceId, provinceName);
                    // Set the province name to the hidden field for submission
                    $('#provinceName').val(provinceName);
                }
            });

            // Event listener for city change to set city name
            $('#d_City').change(function() {
                var cityName = $(this).find(":selected").text(); // Get the city name
                // Set the city name to the hidden field for submission
                $('#cityName').val(cityName);
            });

            // Function to load regions from GeoNames
            function loadRegions() {
                $.ajax({
                    url: 'http://api.geonames.org/childrenJSON',
                    data: {
                        geonameId: 1694008, // Philippines GeoNames ID
                        username: 'velasquezkieren'
                    },
                    success: function(data) {
                        var regionDropdown = $('#d_Region');
                        regionDropdown.empty(); // Clear existing options
                        regionDropdown.append('<option value="" disabled selected>Select Region</option>');
                        $.each(data.geonames, function(i, region) {
                            regionDropdown.append('<option value="' + region.geonameId + '">' + region.name + '</option>');
                        });
                    }
                });
            }

            // Function to load provinces based on selected region
            function loadProvinces(regionId, regionName) {
                $.ajax({
                    url: 'http://api.geonames.org/childrenJSON',
                    data: {
                        geonameId: regionId,
                        username: 'velasquezkieren'
                    },
                    success: function(data) {
                        var provinceDropdown = $('#d_Province');
                        provinceDropdown.empty(); // Clear existing options
                        provinceDropdown.append('<option value="" disabled selected>Select Province</option>');
                        $.each(data.geonames, function(i, province) {
                            var provinceName = province.name.replace(/^Province of\s+/, ''); // Remove "Province of"
                            provinceDropdown.append('<option value="' + province.geonameId + '">' + provinceName + '</option>');
                        });
                    }
                });
            }

            // Function to load cities/municipalities based on selected province
            function loadCities(provinceId, provinceName) {
                $.ajax({
                    url: 'http://api.geonames.org/childrenJSON',
                    data: {
                        geonameId: provinceId,
                        username: 'velasquezkieren'
                    },
                    success: function(data) {
                        var cityDropdown = $('#d_City');
                        cityDropdown.empty(); // Clear existing options
                        cityDropdown.append('<option value="" disabled selected>Select City/Municipality</option>');
                        $.each(data.geonames, function(i, city) {
                            var cityName = city.name.replace(/^(Municipality of|City of)\s+/, ''); // Remove "Municipality of" or "City of"
                            cityDropdown.append('<option value="' + city.geonameId + '">' + cityName + '</option>');
                        });
                    }
                });
            }

            // Initialize Leaflet map
            var map = L.map('map', {
                dragging: true
            }).setView([15.4443, 120.9441], 20); // Cabanatuan, Nueva Ecija

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            // Custom Marker Icon
            var customIcon = L.icon({
                iconUrl: './assets/images/marker-icon-2x-red.png', // Replace with your custom icon path
                iconSize: [25, 41], // size of the icon
                iconAnchor: [19, 38], // point of the icon which will correspond to marker's location
                popupAnchor: [-3, -76], // point from which the popup should open relative to the iconAnchor
            });

            // Add the marker at the center of the screen, but we'll update its position dynamically
            var marker = L.marker(map.getCenter(), {
                icon: customIcon
            }).addTo(map);

            // Update marker position based on map center when the map is moved
            map.on('move', function() {
                var center = map.getCenter();
                marker.setLatLng(center); // Set marker position to the map center
                $('#latitude').val(center.lat);
                $('#longitude').val(center.lng);
            });

            // Image preview functionality
            $('input[name="d_PicName[]"]').on('change', function(event) {
                const imagePreview = $('#image-preview');
                imagePreview.empty(); // Clear previous previews

                $.each(event.target.files, function(index, file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = $('<img>', {
                            src: e.target.result,
                            css: {
                                width: '100px', // Set preview size
                                marginRight: '10px'
                            }
                        });
                        imagePreview.append(img);
                    }
                    reader.readAsDataURL(file);
                });
            });
        });
    </script>
<?php
}
?>