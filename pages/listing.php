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
    $d_Name = mysqli_real_escape_string($con, ucwords($_POST['d_Name']));
    $d_Street = mysqli_real_escape_string($con, ucwords($_POST['d_Street']));
    $d_City = mysqli_real_escape_string($con, ucwords($_POST['d_City']));
    $d_ZIPCode = mysqli_real_escape_string($con, $_POST['d_ZIPCode']);
    $d_Province = mysqli_real_escape_string($con, ucwords($_POST['d_Province']));
    $d_Region = mysqli_real_escape_string($con, ucwords($_POST['d_Region']));
    $d_Description = mysqli_real_escape_string($con, $_POST['d_Description']);
    $d_Availability = '1';  // Default availability status
    $d_Price = mysqli_real_escape_string($con, $_POST['d_Price']);
    $d_Gender = mysqli_real_escape_string($con, $_POST['d_Gender']);
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
        $d_Latitude = mysqli_real_escape_string($con, $_POST['d_Latitude']);
        $d_Longitude = mysqli_real_escape_string($con, $_POST['d_Longitude']);

        // Prepare an SQL statement to insert dormitory details into the database
        $stmt = $con->prepare("INSERT INTO dormitory (d_ID, d_Name, d_Street, d_City, d_ZIPCode, d_Province, d_Region, d_Availability, d_Description, d_Owner, d_PicName, d_Latitude, d_Longitude, d_Price, d_Gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters to the SQL statement
        $stmt->bind_param('sssssssssssssss', $d_ID, $d_Name, $d_Street, $d_City, $d_ZIPCode, $d_Province, $d_Region, $d_Availability, $d_Description, $d_Owner, $d_PicNames, $d_Latitude, $d_Longitude, $d_Price, $d_Gender);

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
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="hidden" name="d_Latitude" id="latitude">
                                                    <input type="hidden" name="d_Longitude" id="longitude">
                                                    <div id="map" class="rounded" style="height: 450px; width:1100px;"></div>
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
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3">
                                                    <input type="number" name="d_Price" class="form-control" id="d_Price" placeholder="Price" required>
                                                    <label for="d_Price">Price</label>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
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