<?php
// Ensure there is a d_ID in the URL
if (!isset($_GET['d_ID']) || empty($_GET['d_ID'])) {
    echo "<script>alert('Invalid request. No dormitory ID provided.');</script>";
    header('location:home');
    exit();
}

$d_ID = $_GET['d_ID'];

// Fetch the dormitory information
$sql = "SELECT * FROM dormitory WHERE d_ID = '$d_ID'";
$result = mysqli_query($con, $sql);
$dormitory = mysqli_fetch_assoc($result);

if (!$dormitory) {
    echo "<p>Dormitory not found.</p>";
    exit();
}

// Get the owner ID of the dormitory
$d_Owner_ID = $dormitory['d_Owner'];
$loggedInUserID = $_SESSION['u_ID']; // Assuming you have this session variable

// Retrieve coordinates and Dorm Name
$latitude = $dormitory['d_Latitude'];
$longitude = $dormitory['d_Longitude'];
$dormName = htmlspecialchars($dormitory['d_Name']); // Sanitize dormitory name

if (isset($_POST['edit_dormitory'])) {
    // Process the form to update the dormitory
    $d_ID = $_POST['d_ID'];
    $d_Name = $_POST['d_Name'];
    $d_Street = $_POST['d_Street'];
    $d_City = $_POST['d_City'];
    $d_Description = $_POST['d_Description'];
    $replaceImages = isset($_POST['replace_images']) ? true : false;
    $d_PicName = $_FILES['d_PicName'];

    // Update dormitory details
    $sql = "UPDATE dormitory SET d_Name = '$d_Name', d_Street = '$d_Street', d_City = '$d_City', d_Description = '$d_Description' WHERE d_ID = '$d_ID'";
    mysqli_query($con, $sql);

    // Handle image uploads
    if ($replaceImages) {
        // Delete old images
        $sql = "SELECT d_PicName FROM dormitory WHERE d_ID = '$d_ID'";
        $result = mysqli_query($con, $sql);
        $dormitory = mysqli_fetch_assoc($result);

        if ($dormitory) {
            $images = explode(',', $dormitory['d_PicName']);
            foreach ($images as $image) {
                $filePath = './upload/' . $d_ID . '/' . $image;
                if (file_exists($filePath)) {
                    unlink($filePath); // Remove the old image file
                }
            }
        }

        // Clear the old image names in the database
        $sql = "UPDATE dormitory SET d_PicName = '' WHERE d_ID = '$d_ID'";
        mysqli_query($con, $sql);
    }

    // Upload new images
    if (!empty($d_PicName['name'][0])) {
        $uploadDir = './upload/' . $d_ID . '/';
        $imageNames = [];
        foreach ($d_PicName['tmp_name'] as $key => $tmpName) {
            $fileName = basename($d_PicName['name'][$key]);
            $uniqueFileName = uniqid() . '@dormease@' . $fileName;
            $uploadFile = $uploadDir . $uniqueFileName;
            move_uploaded_file($tmpName, $uploadFile);
            $imageNames[] = $uniqueFileName;
        }
        $d_PicNames = implode(',', $imageNames);

        if ($replaceImages) {
            // Insert new images names
            $sql = "UPDATE dormitory SET d_PicName = '$d_PicNames' WHERE d_ID = '$d_ID'";
        } else {
            // Append new images names
            $sql = "UPDATE dormitory SET d_PicName = CONCAT_WS(',', d_PicName, '$d_PicNames') WHERE d_ID = '$d_ID'";
        }
        mysqli_query($con, $sql);
    }

    echo "<script>alert('Listing updated successfully.'); window.location.href='property?d_ID=$d_ID';</script>";
    exit();
}

if (isset($_POST['delete_dormitory'])) {
    // Get the dormitory ID from the form
    $d_ID = $_POST['d_ID'];

    // Fetch the dormitory details to get image names
    $sql = "SELECT d_PicName FROM dormitory WHERE d_ID = '$d_ID'";
    $result = mysqli_query($con, $sql);
    $dormitory = mysqli_fetch_assoc($result);

    if ($dormitory) {
        // Delete associated images
        $images = explode(',', $dormitory['d_PicName']);
        foreach ($images as $image) {
            $filePath = './upload/' . $d_ID . '/' . $image; // Add the folder name
            if (file_exists($filePath)) {
                unlink($filePath); // Remove the image file
            }
        }

        // Delete the dormitory record
        $sql = "DELETE FROM dormitory WHERE d_ID = '$d_ID'";
        mysqli_query($con, $sql);

        // Delete the folder
        $folderPath = './upload/' . $d_ID;
        if (is_dir($folderPath)) {
            rmdir($folderPath); // Remove the folder
        }
    }

    // Redirect to profile page
    echo "<script>alert('Listing deleted successfully.'); window.location.href='profile';</script>";
    exit();
}

?>

<div class="container">
    <div class="row pt-5 text-center text-md-start">
        <div class="col-12 col-md pt-5 d-flex flex-column align-items-center align-items-md-start">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="find">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house">
                                <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path>
                                <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            </svg>
                        </a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($dormitory['d_Name']); ?></li>
                </ol>
            </nav>
            <p class="h1"><?php echo htmlspecialchars($dormitory['d_Name']); ?></p>
            <div class="d-flex align-items-center">
                <i class="bi bi-geo-alt-fill"></i>
                <p class="h5 mb-0 ms-2"><?php echo htmlspecialchars($dormitory['d_Street'] . ', ' . $dormitory['d_City']); ?></p>
            </div>
        </div>
        <div class="col-12 col-md-auto pt-3 pt-md-5 d-flex justify-content-md-end justify-content-center align-items-center align-items-md-end">
            <?php
            if ($loggedInUserID == $d_Owner_ID && isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 0) {
                echo '<button class="btn login-button" data-bs-toggle="modal" data-bs-target="#editListingModal">Edit Listing</button>';
            } elseif (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 1) {
                echo '<a class="login-button" href="list.php?book&d_ID=' . urlencode($d_ID) . '">Book now 2,000/month</a>';
            }
            ?>
        </div>
    </div>
    <div class="row pt-3">
        <div class="col-12">
            <div id="dormitoryCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php
                    $images = explode(',', $dormitory['d_PicName']);
                    $isActive = 'active';
                    foreach ($images as $image) {
                        echo '<div class="carousel-item ' . $isActive . '">';
                        echo '<img src="./upload/' . $d_ID . '/' . htmlspecialchars($image) . '" class="d-block w-100" alt="Dormitory Image">';
                        echo '</div>';
                        $isActive = '';
                    }
                    ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#dormitoryCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#dormitoryCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </div>

    <div class="row pt-5">
        <p class="h1">About</p>
        <div class="col-12 col-md">
            <p><?php echo nl2br(htmlspecialchars($dormitory['d_Description'])); ?></p>
        </div>
        <div class="col-12">
            <h2>Location</h2>
            <!-- OpenStreetMap Integration here -->
            <div id="map" style="height: 400px;"></div>
        </div>
        <div class="col-12 col-md">
            <h2>Available Rooms</h2>
        </div>
    </div>
</div>

<!-- Edit Listing Modal -->
<div class="modal" id="editListingModal" tabindex="-1" aria-labelledby="editListingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editListingModalLabel">Edit Listing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editListingForm" method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="d_ID" value="<?php echo htmlspecialchars($d_ID); ?>">

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="d_Name" name="d_Name" placeholder="Dormitory Name" value="<?php echo htmlspecialchars($dormitory['d_Name']); ?>" required>
                        <label for="d_Name">Dormitory Name</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="d_Street" name="d_Street" placeholder="Street" value="<?php echo htmlspecialchars($dormitory['d_Street']); ?>" required>
                        <label for="d_Street">Street</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="d_City" name="d_City" placeholder="City" value="<?php echo htmlspecialchars($dormitory['d_City']); ?>" required>
                        <label for="d_City">City</label>
                    </div>

                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="d_Description" name="d_Description" placeholder="Description" rows="4" required><?php echo htmlspecialchars($dormitory['d_Description']); ?></textarea>
                        <label for="d_Description">Description</label>
                    </div>

                    <div class="mb-3">
                        <label for="d_PicName" class="form-label">Images</label>
                        <input type="file" class="form-control" id="d_PicName" name="d_PicName[]" multiple>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="replace_images" name="replace_images">
                        <label class="form-check-label" for="replace_images">Replace all existing images</label>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteListingModal">Delete Listing</button>
                        <button type="submit" name="edit_dormitory" class="btn btn-dark">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteListingModal" tabindex="-1" aria-labelledby="deleteListingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteListingModalLabel">Delete Listing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this listing? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteListingForm" method="POST" action="">
                    <input type="hidden" name="d_ID" value="<?php echo htmlspecialchars($d_ID); ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_dormitory" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize the map
    var map = L.map('map').setView([<?php echo htmlspecialchars($latitude); ?>, <?php echo htmlspecialchars($longitude); ?>], 19);

    // Add tile layer
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    // Add marker and display dorm name
    L.marker([<?php echo htmlspecialchars($latitude); ?>, <?php echo htmlspecialchars($longitude); ?>]).addTo(map)
        .bindPopup('<?php echo $dormName; ?>')
        .openPopup();
</script>