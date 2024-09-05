<?php
// Redirect students away from this page
if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 1) {
    header('Location: home');
    exit();
}

// Handle form submission
if (isset($_POST['submit'])) {

    // Dorm Details form
    $d_ID = uniqid('d_');
    $d_Name = mysqli_real_escape_string($con, $_POST['d_Name']);
    $d_Street = mysqli_real_escape_string($con, $_POST['d_Street']);
    $d_City = mysqli_real_escape_string($con, $_POST['d_City']);
    $d_ZIPCode = mysqli_real_escape_string($con, $_POST['d_ZIPCode']);
    $d_Province = mysqli_real_escape_string($con, $_POST['d_Province']);
    $d_Region = mysqli_real_escape_string($con, $_POST['d_Region']);
    $d_Latitude = mysqli_real_escape_string($con, $_POST['d_Latitude']);
    $d_Longitude = mysqli_real_escape_string($con, $_POST['d_Longitude']);
    $d_Description = mysqli_real_escape_string($con, $_POST['d_Description']);
    $d_Availability = '1';  // Default availability
    $d_Owner = $_SESSION['u_ID'];  // Assuming the user ID is stored in session

    // Handle multiple image uploads
    $imageNames = [];
    foreach ($_FILES['d_PicName']['tmp_name'] as $key => $tmp_name) {
        $file_name = $_FILES['d_PicName']['name'][$key];
        $file_tmp = $_FILES['d_PicName']['tmp_name'][$key];
        $file_directory = "upload/";

        // Generate a unique name for the image to avoid overwriting
        $uniqueFileName = uniqid() . '@dormease@' . $file_name;

        // Move the uploaded file to the desired directory
        if (move_uploaded_file($file_tmp, $file_directory . $uniqueFileName)) {
            // Collect the image name
            $imageNames[] = $uniqueFileName;
        } else {
            echo '<script>alert("Error uploading image: ' . htmlspecialchars($file_name) . '");</script>';
            exit();
        }
    }

    // Convert image names array to a comma-separated string
    $d_PicNames = implode(',', $imageNames);

    // Insert dormitory details into the database
    $sql = "INSERT INTO dormitory (d_ID, d_Name, d_Street, d_City, d_ZIPCode, d_Province, d_Region, d_Latitude, d_Longitude, d_Availability, d_Description, d_Owner, d_PicName)
            VALUES ('$d_ID', '$d_Name', '$d_Street', '$d_City', '$d_ZIPCode', '$d_Province', '$d_Region', '$d_Latitude', '$d_Longitude', '$d_Availability', '$d_Description', '$d_Owner', '$d_PicNames')";

    if (mysqli_query($con, $sql)) {
        header("Location: property?d_ID=" . $d_ID);
        exit();
    } else {
        echo "<script>(`Error: " . mysqli_error($con) . "`); </script>";
    }
}
?>

<?php
if (!isset($_SESSION['u_Email'])) {
?>
    <!-- top section -->
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
<?php
} else {
?>
    <section class="p-3 p-md-4 p-xl-5 bg-light-subtle">
        <div class="container" style="margin-top:100px;">
            <div class="row justify-content-center">
                <div class="col-12 col-xxl-11" id="list-property">
                    <h2 class="h2 mb-4">Tell us about your property!</h2>
                    <div class="card border-light-subtle shadow-sm">
                        <div class="row g-0">
                            <div class="col-12">
                                <div class="card-body p-3 p-md-4 p-xl-5">
                                    <form action="" class="row g-3" enctype="multipart/form-data" method="post">
                                        <ul class="nav nav-tabs">
                                            <li class="nav-item">
                                                <a class="nav-link active_tab1" id="list_dorm_name" href="#dorm_name" data-toggle="tab">Dorm Name</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link inactive_tab1" id="list_dorm_details" href="#dorm_details" data-toggle="tab">Dorm Details</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link inactive_tab1" id="list_images" href="#images" data-toggle="tab">Images</a>
                                            </li>
                                        </ul>
                                        <hr style="border: 1px solid black;">

                                        <div class="tab-content">
                                            <!-- Dorm Name Tab -->
                                            <div class="tab-pane active" id="dorm_name">
                                                <div class="col-12">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" name="d_Name" class="form-control" id="floatingInput" placeholder="Dorm Name" required>
                                                        <label for="floatingInput">Dorm Name</label>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-primary btn-sm" id="btn_dorm_name">Next</button>
                                            </div>

                                            <!-- Dorm Details Tab -->
                                            <div class="tab-pane" id="dorm_details">
                                                <div class="col-12">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" name="d_Street" class="form-control" id="d_Street" placeholder="Street Address" required>
                                                        <label for="d_Street">Street Address</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" name="d_City" class="form-control" id="d_City" placeholder="City" required>
                                                        <label for="d_City">City</label>
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
                                                        <input type="text" name="d_Province" class="form-control" id="d_Province" placeholder="Province" required>
                                                        <label for="d_Province">Province</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" name="d_Region" class="form-control" id="d_Region" placeholder="Region" required>
                                                        <label for="d_Region">Region</label>
                                                    </div>
                                                </div>
                                                <!-- Map for location selection -->
                                                <div class="col-12">
                                                    <div id="map" style="width: 100%; height: 400px;"></div>
                                                    <input type="hidden" id="latitude" name="d_Latitude">
                                                    <input type="hidden" id="longitude" name="d_Longitude">
                                                </div>
                                                <button type="button" class="btn btn-info btn-sm" id="previous_btn_dorm_details">Previous</button>
                                                <button type="button" class="btn btn-primary btn-sm" id="btn_dorm_details">Next</button>
                                            </div>

                                            <!-- Images Tab -->
                                            <div class="tab-pane" id="images">
                                                <div class="col-12">
                                                    <input type="file" name="d_PicName[]" accept="image/*" multiple required>
                                                </div>
                                                <button type="button" class="btn btn-info btn-sm" id="previous_btn_images">Previous</button>
                                                <button type="submit" class="btn btn-dark" name="submit">Submit</button>
                                            </div>
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
<?php
}
?>

<script>
    // Initialize Leaflet map
    var map = L.map('map', {
        dragging: true
    }).setView([15.4443, 120.9435], 20); // Cabanatuan, Nueva Ecija

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 30,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    // Custom Marker Icon
    var customIcon = L.icon({
        iconUrl: './css/marker-icon-2x.png', // Replace with your custom icon path
        iconSize: [38, 38], // size of the icon
        iconAnchor: [19, 38], // point of the icon which will correspond to marker's location
        popupAnchor: [-3, -76], // point from which the popup should open relative to the iconAnchor
        shadowUrl: './css/marker-shadow.png', // optional shadow image
        shadowSize: [50, 64], // size of the shadow
        shadowAnchor: [4, 62] // the same for the shadow
    });

    // Add the marker at the center of the screen, but we'll update its position dynamically
    var marker = L.marker(map.getCenter(), {
        icon: customIcon
    }).addTo(map);

    // Update marker position based on map center when map is moved
    map.on('move', function() {
        var center = map.getCenter();
        marker.setLatLng(center); // Set marker position to the map center
        document.getElementById('latitude').value = center.lat;
        document.getElementById('longitude').value = center.lng;
    });

    // Form navigation script
    $('#btn_dorm_name').click(function() {
        $('.nav-tabs a[href="#dorm_details"]').tab('show');
    });
    $('#btn_dorm_details').click(function() {
        $('.nav-tabs a[href="#images"]').tab('show');
    });
    $('#previous_btn_dorm_details').click(function() {
        $('.nav-tabs a[href="#dorm_name"]').tab('show');
    });
    $('#previous_btn_images').click(function() {
        $('.nav-tabs a[href="#dorm_details"]').tab('show');
    });
</script>