<?php
// Ensure there is a d_ID in the URL
if (!isset($_GET['d_ID']) || empty($_GET['d_ID'])) {
    echo "<script>alert('Invalid request. No dormitory ID provided.');</script>";
    header('location:login?auth-required');
    exit();
}

// Get Property ID
$d_ID = $_GET['d_ID'];

// Fetch the dormitory information
$sql = "SELECT * FROM dormitory WHERE d_ID = '$d_ID'";
$result = mysqli_query($con, $sql);
$dormitory = mysqli_fetch_assoc($result);

if (!$dormitory) {
    header("location: find");
    exit();
}

// Get the owner ID of the dormitory
$d_Owner_ID = $dormitory['d_Owner'];
// Query to fetch owner details (u_Picture, u_ContactNumber, and full name)
$owner_query = mysqli_query($con, "SELECT u_FName, u_MName, u_LName, u_Picture, u_ContactNumber FROM user WHERE u_ID = '$d_Owner_ID'");

// Check if the user is logged in
if (isset($_SESSION['u_ID'])) {
    $loggedInUserID = $_SESSION['u_ID']; // Assuming you have this session variable
} else {
    $loggedInUserID = null; // Set it to null if not logged in
}

// Retrieve coordinates, Dorm Name, Price, and Gender
$latitude = $dormitory['d_Latitude'];
$longitude = $dormitory['d_Longitude'];
$dormName = htmlspecialchars($dormitory['d_Name']);
$d_Price = htmlspecialchars($dormitory['d_Price']);
$d_Amenities = $dormitory['d_Amenities']; // Retrieved as a delimited string
$d_Gender = $dormitory['d_Gender'];

// Check if the logged-in user is the owner
$isOwner = ($loggedInUserID !== null && $loggedInUserID === $d_Owner_ID);

if ($isOwner) {
    // Show all rooms for the owner, including available and pending rooms
    $roomsSql = "SELECT * FROM room WHERE r_Dormitory = '$d_ID'";
} else {
    // Show only available rooms with registration status 1 for non-owners
    $roomsSql = "SELECT * FROM room WHERE r_Dormitory = '$d_ID' AND r_RegistrationStatus = 1";
}

// Fetch rooms
$roomsResult = mysqli_query($con, $roomsSql);

// Edit Dormitory
if (isset($_POST['edit_dormitory'])) {
    // Process the form to update the dormitory
    $d_ID = $_POST['d_ID'];
    $d_Name = ucwords($_POST['d_Name']);
    $d_Street = ucwords($_POST['d_Street']);
    $d_City = ucwords($_POST['d_City']);
    $d_Description = $_POST['d_Description'];
    $d_Price = $_POST['d_Price'];
    $d_Gender = $_POST['d_Gender'];
    $d_Availability = $_POST['d_Availability'];
    $replaceImages = isset($_POST['replace_images']) ? true : false;
    $d_PicName = $_FILES['d_PicName'];

    // Update dormitory details using prepared statements to prevent SQL injection
    $stmt = $con->prepare("UPDATE dormitory SET d_Name = ?, d_Street = ?, d_City = ?, d_Description = ?, d_Price = ?, d_Gender = ?, d_Availability = ? WHERE d_ID = ?");
    $stmt->bind_param('ssssdiss', $d_Name, $d_Street, $d_City, $d_Description, $d_Price, $d_Gender, $d_Availability, $d_ID);
    $stmt->execute();

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

// Delete Dormitory
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

        // Delete associated rooms first
        $deleteRoomsSql = "DELETE FROM room WHERE r_Dormitory = '$d_ID'";
        mysqli_query($con, $deleteRoomsSql);

        // Now delete the dormitory record
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

// Fetch exchange rates
$api_url = 'https://v6.exchangerate-api.com/v6/4f15e2dd8cbbd191697f4229/latest/PHP';
$response = file_get_contents($api_url);
$data = json_decode($response, true);

// Check if the response is valid
if ($data && $data['result'] == 'success') {
    $usd_rate = $data['conversion_rates']['USD']; // Get USD rate
    $cny_rate = $data['conversion_rates']['CNY']; // Get CNY rate
} else {
    $usd_rate = 0; // Default value if API fails
    $cny_rate = 0; // Default value if API fails
}

// Convert PHP price to other currencies
$d_Price = htmlspecialchars($dormitory['d_Price']);
$usd_price = $d_Price * $usd_rate;
$cny_price = $d_Price * $cny_rate;

// Schedule a visit (for tenants)
if (isset($_POST['schedule'])) {
    // Collect form data
    $visitorID = $_POST['visitorID'];
    $landlordID = $_POST['landlordID'];
    $visitDateTime = $_POST['visitDateTime'];
    $d_ID = $_POST['d_ID'];
    $v_Status = 2;

    // Generate a unique ID for the visit (you can adjust this as needed)
    $visitID = uniqid('v_', true);

    // Prepare an SQL statement to prevent SQL injection
    $stmt = $con->prepare("INSERT INTO visit (v_ID, v_Visitor, v_Landlord, v_DateTime, v_Dormitory, v_Status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $visitID, $visitorID, $landlordID, $visitDateTime, $d_ID, $v_Status);

    // Execute the statement
    if ($stmt->execute()) {
        header("location: scheduled-visits?schedule-success");
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $con->close();
}

// Inquiry (for tenants)
if (isset($_POST['inquire'])) {
    // Collect inquiry details
    $inquiryMessage = mysqli_real_escape_string($con, trim($_POST['inquiryMessage']));
    $senderID = $loggedInUserID; // ID of the logged-in user
    $recipientID = $d_Owner_ID; // ID of the landlord

    // Generate a unique ID for the message
    $messageID = uniqid('m_', true);
    $dateTime = date('Y-m-d H:i:s');

    // Prepare and execute SQL statement to insert the inquiry message
    $stmt = $con->prepare("INSERT INTO messaging (m_ID, m_Recipient, m_Sender, m_Message, m_DateTime) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $messageID, $recipientID, $senderID, $inquiryMessage, $dateTime);

    if ($stmt->execute()) {
        header("Location: messages?m_ID=$messageID");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Book a room (for tenants)
if (isset($_POST['book'])) {
    $o_Name = $_POST['o_Name'];  // This is now the room ID (r_ID)
    $o_Occupant = $_POST['o_Occupant'];
    $o_Status = 0;  // Set default status as unoccupied or new booking

    $stmt = $con->prepare("INSERT INTO occupancy (o_Room, o_Occupant, o_Status) VALUES (?,?,?)");
    $stmt->bind_param("ssi", $o_Name, $o_Occupant, $o_Status);

    if ($stmt->execute()) {
        header("Location: bookings?book-success");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<div class="container min-vh-100">
    <div class="row pt-5 text-center text-md-start">
        <div class="col-12 col-md pt-5 d-flex flex-column align-items-center align-items-md-start">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="find">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house" style="color: black;">
                                <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path>
                                <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            </svg>

                        </a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($dormitory['d_Name']); ?></li>
                </ol>
            </nav>

        </div>
        <div class="col-12 col-md-auto pt-3 pt-md-5 d-flex justify-content-md-end justify-content-center align-items-center align-items-md-end">
            <?php
            if ($loggedInUserID == $d_Owner_ID && isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 0) : ?>
                <button class="btn login-button" data-bs-toggle="modal" data-bs-target="#editListingModal">Edit Listing</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Picture display -->
    <div class="row pt-3">
        <div class="col-12">
            <!-- Carousel for mobile users -->
            <div id="dormitoryCarousel" class="carousel slide d-md-none" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php
                    $imageNames = explode(',', $dormitory['d_PicName']);
                    foreach ($imageNames as $index => $imgName):
                        $activeClass = ($index === 0) ? 'active' : '';
                    ?>
                        <div class="carousel-item <?php echo $activeClass; ?>">
                            <a href="#" class="open-modal" data-index="<?php echo $index; ?>">
                                <img src="./upload/<?php echo $d_ID; ?>/<?php echo htmlspecialchars($imgName); ?>" class="d-block w-100" loading=" lazy" alt="<?= $dormName; ?>">
                            </a>
                        </div>
                    <?php endforeach; ?>
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

            <!-- Grid of cards for desktop users -->
            <div class="row d-none d-md-flex">
                <div class="col-md-6 mb-3">
                    <a href="#" class="open-modal" data-index="0">
                        <div class="card" style="border: none; transition: transform 0.2s;">
                            <img src="./upload/<?php echo $d_ID; ?>/<?php echo htmlspecialchars($imageNames[0]); ?>" class="card-img-top rounded" loading="lazy" alt="<?= $dormName; ?>" style="object-fit: cover; height: 420px;">
                        </div>
                    </a>
                </div>

                <div class="col-md-6">
                    <div class="row">
                        <?php
                        // Loop through remaining images
                        for ($i = 1; $i < count($imageNames); $i++):
                            $columnClass = (count($imageNames) < 4 && $i < 3) ? '12' : '6';
                        ?>
                            <div class="col-<?php echo $columnClass; ?> mb-3">
                                <a href="#" class="open-modal" data-index="<?php echo $i; ?>">
                                    <div class="card" style="border: none; transition: transform 0.2s;">
                                        <img src="./upload/<?php echo $d_ID; ?>/<?php echo htmlspecialchars($imageNames[$i]); ?>" class="card-img-top rounded" loading="lazy" alt="<?= $dormName; ?>" style="object-fit: cover; height: 200px;">
                                    </div>
                                </a>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Carousel -->
    <div class="modal" id="imageCarouselModal" tabindex="-1" aria-labelledby="imageCarouselModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <div id="modalCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php
                            foreach ($imageNames as $index => $imgName):
                                $activeClass = ($index === 0) ? 'active' : '';
                            ?>
                                <div class="carousel-item <?php echo $activeClass; ?>">
                                    <img src="./upload/<?php echo $d_ID; ?>/<?php echo htmlspecialchars($imgName); ?>"
                                        class="d-block w-100 img-fluid" loading="lazy" alt="<?= htmlspecialchars($dormName); ?>"
                                        style="object-fit: contain; max-height: 70vh; margin: 0 auto;">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#modalCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#modalCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dorm Details -->
    <div class="row pt-2">
        <!-- Full width on mobile, 80% on medium and large screens -->
        <div class="col-12 col-md-8 col-lg-8">
            <div class="row">
                <!-- Owner Details -->
                <!-- Full width on mobile, half on medium screens -->
                <div class="col-12 col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body shadow-sm"> <!-- Center content for mobile view -->
                            <?php
                            // Check if the query returned results
                            if ($owner_data = mysqli_fetch_assoc($owner_query)) {
                                // Fetch owner details
                                $owner_full_name = htmlspecialchars($owner_data['u_FName'] . ' ' . $owner_data['u_MName'] . ' ' . $owner_data['u_LName']);
                                $owner_picture = htmlspecialchars($owner_data['u_Picture']);
                                $owner_contact = htmlspecialchars($owner_data['u_ContactNumber']);
                            }
                            ?>
                            <h5 class="card-title d-flex align-items-center"> <!-- Flex for alignment -->
                                <img src="user_avatar/<?= $owner_picture; ?>" alt="<?= $owner_full_name ?>" class="img-fluid rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <?= $owner_full_name; ?>
                            </h5>
                            <p class="card-text">
                                <i class="bi bi-telephone"></i> <?= $owner_contact; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Dormitory Details -->
                <div class="col-12"> <!-- Full width on all screen sizes -->
                    <p class="h3 mb-0"><?php echo htmlspecialchars($dormitory['d_Name']); ?></p>
                    <span class="badge text-bg-secondary mb-1">
                        <?php
                        if ($d_Gender == 0) {
                            echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gender-female" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M8 1a4 4 0 1 0 0 8 4 4 0 0 0 0-8M3 5a5 5 0 1 1 5.5 4.975V12h2a.5.5 0 0 1 0 1h-2v2.5a.5.5 0 0 1-1 0V13h-2a.5.5 0 0 1 0-1h2V9.975A5 5 0 0 1 3 5"/>
</svg>
Female';
                        } elseif ($d_Gender == 1) {
                            echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gender-male" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M9.5 2a.5.5 0 0 1 0-1h5a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-1 0V2.707L9.871 6.836a5 5 0 1 1-.707-.707L13.293 2zM6 6a4 4 0 1 0 0 8 4 4 0 0 0 0-8"/>
</svg>
Male';
                        } else {
                            echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gender-ambiguous" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M11.5 1a.5.5 0 0 1 0-1h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V1.707l-3.45 3.45A4 4 0 0 1 8.5 10.97V13H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V14H6a.5.5 0 0 1 0-1h1.5v-2.03a4 4 0 1 1 3.471-6.648L14.293 1zm-.997 4.346a3 3 0 1 0-5.006 3.309 3 3 0 0 0 5.006-3.31z"/>
</svg>
Any';
                        }
                        ?>
                    </span>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-geo-alt-fill"></i>
                        <p class="h5 mb-0 ms-1"><?php echo htmlspecialchars($dormitory['d_Street'] . ', ' . $dormitory['d_City']); ?></p>
                    </div>

                </div>

                <!-- Dorm Description -->
                <div class="col-12 mt-4">
                    <p class="h4">Description</p>
                    <p class="card-text text-truncate"><?php echo nl2br(htmlspecialchars($dormitory['d_Description'])); ?></p>
                </div>

                <!-- Dorm Amenities Section -->
                <div class="col-12 mt-4">
                    <p class="h4">What this place offers</p>
                    <ul class="list-group list-group-flush">
                        <?php
                        if (!empty($d_Amenities)) {
                            $amenities = explode(',', $d_Amenities); // Convert amenities string to array
                            foreach ($amenities as $amenity) {
                                $amenity = trim($amenity); // Trim whitespace
                                $icon = ""; // Initialize icon

                                // Map predefined amenities to corresponding SVGs
                                switch (strtolower($amenity)) {
                                    case 'wi-fi':
                                        $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-wifi" viewBox="0 0 16 16">
                                    <path d="M15.384 6.115a.485.485 0 0 0-.047-.736A12.44 12.44 0 0 0 8 3C5.259 3 2.723 3.882.663 5.379a.485.485 0 0 0-.048.736.52.52 0 0 0 .668.05A11.45 11.45 0 0 1 8 4c2.507 0 4.827.802 6.716 2.164.205.148.49.13.668-.049" />
                                    <path d="M13.229 8.271a.482.482 0 0 0-.063-.745A9.46 9.46 0 0 0 8 6c-1.905 0-3.68.56-5.166 1.526a.48.48 0 0 0-.063.745.525.525 0 0 0 .652.065A8.46 8.46 0 0 1 8 7a8.46 8.46 0 0 1 4.576 1.336c.206.132.48.108.653-.065m-2.183 2.183c.226-.226.185-.605-.1-.75A6.5 6.5 0 0 0 8 9c-1.06 0-2.062.254-2.946.704-.285.145-.326.524-.1.75l.015.015c.16.16.407.19.611.09A5.5 5.5 0 0 1 8 10c.868 0 1.69.201 2.42.56.203.1.45.07.61-.091zM9.06 12.44c.196-.196.198-.52-.04-.66A2 2 0 0 0 8 11.5a2 2 0 0 0-1.02.28c-.238.14-.236.464-.04.66l.706.706a.5.5 0 0 0 .707 0l.707-.707z" />
                                </svg>';
                                        break;
                                    case 'parking':
                                        $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-car-front-fill" viewBox="0 0 16 16">
                                    <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679q.05.242.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.8.8 0 0 0 .381-.404l.792-1.848ZM3 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2m10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2M6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2zM2.906 5.189a.51.51 0 0 0 .497.731c.91-.073 3.35-.17 4.597-.17s3.688.097 4.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 11.691 3H4.309a.5.5 0 0 0-.447.276L2.906 5.19Z" />
                                </svg>';
                                        break;
                                    case 'furniture':
                                        $icon = '<svg height="16" width="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path d="M2 2H0V14H2V12H14V14H16V9C16 7.34315 14.6569 6 13 6H6C6 4.89543 5.10457 4 4 4H2V2Z" fill="#000000"></path>
                                    </g>
                                </svg>';
                                        break;
                                    case 'pet-friendly':
                                        $icon = '<svg height="16" width="16" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 48.839 48.839" xml:space="preserve" fill="#000000">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <g>
                                            <path style="fill:#030104;" d="M39.041,36.843c2.054,3.234,3.022,4.951,3.022,6.742c0,3.537-2.627,5.252-6.166,5.252 c-1.56,0-2.567-0.002-5.112-1.326c0,0-1.649-1.509-5.508-1.354c-3.895-0.154-5.545,1.373-5.545,1.373 c-2.545,1.323-3.516,1.309-5.074,1.309c-3.539,0-6.168-1.713-6.168-5.252c0-1.791,0.971-3.506,3.024-6.742 c0,0,3.881-6.445,7.244-9.477c2.43-2.188,5.973-2.18,5.973-2.18h1.093v-0.001c0,0,3.698-0.009,5.976,2.181 C35.059,30.51,39.041,36.844,39.041,36.843z M16.631,20.878c3.7,0,6.699-4.674,6.699-10.439S20.331,0,16.631,0 S9.932,4.674,9.932,10.439S12.931,20.878,16.631,20.878z M10.211,30.988c2.727-1.259,3.349-5.723,1.388-9.971 s-5.761-6.672-8.488-5.414s-3.348,5.723-1.388,9.971C3.684,29.822,7.484,32.245,10.211,30.988z M32.206,20.878 c3.7,0,6.7-4.674,6.7-10.439S35.906,0,32.206,0s-6.699,4.674-6.699,10.439C25.507,16.204,28.506,20.878,32.206,20.878z M45.727,15.602c-2.728-1.259-6.527,1.165-8.488,5.414s-1.339,8.713,1.389,9.972c2.728,1.258,6.527-1.166,8.488-5.414 S48.455,16.861,45.727,15.602z"></path>
                                        </g>
                                    </g>
                                </svg>';
                                        break;
                                    case 'curfew':
                                        $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16">
                                    <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z" />
                                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0" />
                                </svg>';
                                        break;
                                    case 'water bill included':
                                        $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-droplet-fill" viewBox="0 0 16 16">
                                    <path d="M8 16a6 6 0 0 0 6-6c0-1.655-1.122-2.904-2.432-4.362C10.254 4.176 8.75 2.503 8 0c0 0-6 5.686-6 10a6 6 0 0 0 6 6M6.646 4.646l.708.708c-.29.29-1.128 1.311-1.907 2.87l-.894-.448c.82-1.641 1.717-2.753 2.093-3.13" />
                                </svg>';
                                        break;
                                    case 'electric bill included':
                                        $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lightning-charge-fill" viewBox="0 0 16 16">
                                    <path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z" />
                                </svg>';
                                        break;
                                    case 'other':
                                        $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                    <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" />
                                </svg>';
                                        break;
                                    default:
                                        $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                    <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" />
                                </svg>';
                                        break; // Fallback icon
                                }

                                echo "
    <li class='list-group-item'>
        <i class='me-2'>$icon</i>" . htmlspecialchars($amenity) . "
    </li>";
                            }
                        } else {
                            echo "<p class='text-muted'>No amenities listed.</p>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-4 col-lg-4"> <!-- Right column at 20% -->
            <!-- Price Card -->
            <div class="card mb-1 shadow-sm">
                <div class="card-body">
                    <p class="h5 card-title">Price</p>
                    <p class="fw-bold h2">
                        <span class="flag-icon flag-icon-ph"></span> ₱<?php echo number_format($d_Price, 2); ?>/month
                    </p>
                    <p class="fw-bold h4 text-muted">
                        <span class="flag-icon flag-icon-us"></span> $<?php echo number_format($usd_price, 2); ?>,
                        <span class="flag-icon flag-icon-cn"></span> ¥<?php echo number_format($cny_price, 2); ?>
                    </p>
                </div>
            </div>


            <!-- Inquiry & Schedule Card -->
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active card-title" id="schedule-visit-tab" data-bs-toggle="tab" href="#schedule-visit" role="tab" aria-controls="schedule-visit" aria-selected="true">Schedule a Visit</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link card-title" id="inquiry-tab" data-bs-toggle="tab" href="#inquiry" role="tab" aria-controls="inquiry" aria-selected="false">Inquiry</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <!-- Schedule a Visit Tab -->
                        <div class="tab-pane fade show active" id="schedule-visit" role="tabpanel" aria-labelledby="schedule-visit-tab">
                            <?php if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 1) : // Check if the user is a tenant 
                            ?>
                                <form action="" method="POST">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar3 me-2 mt-3" viewBox="0 0 16 16">
                                            <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z" />
                                            <path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
                                        </svg>
                                        <h5 class="mb-0 mt-3">Book an On-Site Viewing</h5>
                                    </div>
                                    <p class="card-text">Select your preferred viewing date</p>

                                    <input type="hidden" name="visitorID" value="<?php echo $loggedInUserID; ?>">
                                    <input type="hidden" name="landlordID" value="<?php echo $d_Owner_ID; ?>">
                                    <input type="hidden" name="d_ID" value="<?php echo $d_ID; ?>">
                                    <div class="mb-3 mt-2">
                                        <label for="visitDateTime" class="form-label">Date and Time</label>
                                        <input type="datetime-local" class="form-control" id="visitDateTime" name="visitDateTime" required>
                                    </div>
                                    <button type="submit" name="schedule" class="btn btn-dark mt-auto">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-check me-1" viewBox="0 0 16 16">
                                            <path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0" />
                                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z" />
                                        </svg>
                                        Schedule Visit
                                    </button>
                                </form>
                            <?php else : ?>
                                <div class="alert alert-secondary mt-3" role="alert">Only logged-in tenants can schedule visits.</div>
                            <?php endif; ?>
                        </div>

                        <!-- Inquiry Tab -->
                        <div class="tab-pane fade" id="inquiry" role="tabpanel" aria-labelledby="inquiry-tab">
                            <?php if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 1) : // Check if the user is a tenant 
                            ?>
                                <form action="" method="POST">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat-dots me-2 mt-3" viewBox="0 0 16 16">
                                            <path d="M5 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0m4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
                                            <path d="m2.165 15.803.02-.004c1.83-.363 2.948-.842 3.468-1.105A9 9 0 0 0 8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6a10.4 10.4 0 0 1-.524 2.318l-.003.011a11 11 0 0 1-.244.637c-.079.186.074.394.273.362a22 22 0 0 0 .693-.125m.8-3.108a1 1 0 0 0-.287-.801C1.618 10.83 1 9.468 1 8c0-3.192 3.004-6 7-6s7 2.808 7 6-3.004 6-7 6a8 8 0 0 1-2.088-.272 1 1 0 0 0-.711.074c-.387.196-1.24.57-2.634.893a11 11 0 0 0 .398-2" />
                                        </svg>
                                        <h5 class="mb-0 mt-3">Make an Inquiry</h5>
                                    </div>
                                    <p class="card-text">Need clarifications about this listing?</p>
                                    <div class="mb-3 mt-2">
                                        <label for="inquiryMessage" class="form-label">Your Message</label>
                                        <textarea class="form-control" id="inquiryMessage" name="inquiryMessage" rows="3" required></textarea>
                                    </div>
                                    <input type="hidden" name="visitorID" value="<?php echo $loggedInUserID; ?>">
                                    <input type="hidden" name="landlordID" value="<?php echo $d_Owner_ID; ?>">
                                    <button type="submit" name="inquire" class="btn btn-dark mt-auto">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16">
                                            <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z" />
                                        </svg>
                                        Send Inquiry
                                    </button>
                                </form>
                            <?php else : ?>
                                <div class="alert alert-secondary mt-3" role="alert">Only logged-in tenants can send inquiries.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map -->
    <div class="row mt-4 mb-4">
        <!-- Map -->
        <div class="col-12">
            <h2>Location</h2>
            <!-- OpenStreetMap Integration here -->
            <div id="map" class="rounded" style="height: 400px;"></div>
        </div>
    </div>

    <!-- Rooms Section -->
    <div class="col-12 col-md">
        <h3 class="mb-4">Available Rooms</h3>
        <div class="row">
            <?php if (mysqli_num_rows($roomsResult) > 0): ?>
                <?php while ($room = mysqli_fetch_assoc($roomsResult)): ?>
                    <div class="col-12 col-md-4 mb-4">
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-body bg-light">
                                <h5 class="card-title"><?php echo htmlspecialchars($room['r_Name']); ?></h5>
                                <p class="card-text"><span class="badge bg-secondary "><?php echo htmlspecialchars($room['r_Capacity']); ?> Available</span></p>
                                <p class="card-text text-truncate"><?php echo htmlspecialchars($room['r_Description']); ?></p>

                                <?php if ($isOwner && $room['r_RegistrationStatus'] == 0): ?>
                                    <p class="card-text"><span class="badge bg-warning">Pending</span></p>
                                <?php endif; ?>

                                <?php if ($isOwner): ?>
                                    <a href="delete-room?r_ID=<?php echo htmlspecialchars($room['r_ID']); ?>&d_ID=<?php echo htmlspecialchars($d_ID); ?>" class="text-danger">Delete Room</a>
                                <?php endif; ?>

                                <form action="" method="post">
                                    <input type="hidden" name="o_Name" value="<?php echo htmlspecialchars($room['r_ID']); ?>">
                                    <input type="hidden" name="o_Occupant" value="<?php echo $loggedInUserID; ?>">
                                    <?php if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 1 && $room['r_Capacity'] > 0): // Check if the user is a tenant and the room is available 
                                    ?>
                                        <button type="submit" name="book" class="btn btn-dark mt-auto">Book Now ₱ <?php echo htmlspecialchars($d_Price); ?>/month</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-secondary text-center p-5" role="alert">
                        No available rooms at the moment.
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="row">
            <?php if ($isOwner): ?>
                <div class="col-md-12">
                    <div class="card mb-3">
                        <div class="card-body text-center">
                            <h5 class="card-title">Add a Room</h5>
                            <p class="card-text">You can add a new room to this dormitory.</p>
                            <a href="add-room?d_ID=<?php echo htmlspecialchars($d_ID); ?>" class="btn btn-success">Add Room</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Edit Listing Modal -->
<div class="modal" id="editListingModal" tabindex="-1" aria-labelledby="editListingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editListingModalLabel">You are editing: <?php echo htmlspecialchars($dormitory['d_Name']); ?></h5>
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

                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="d_Price" name="d_Price" placeholder="Price" value="<?php echo htmlspecialchars($dormitory['d_Price']); ?>" required>
                        <label for="d_Price">Price (per month)</label>
                    </div>

                    <div class="mb-3">
                        <label for="d_Gender" class="form-label">Gender Specification</label>
                        <select class="form-select" id="d_Gender" name="d_Gender" required>
                            <option value="2" <?php echo ($dormitory['d_Gender'] == 2) ? 'selected' : ''; ?>>Any</option>
                            <option value="1" <?php echo ($dormitory['d_Gender'] == 1) ? 'selected' : ''; ?>>Male Only</option>
                            <option value="0" <?php echo ($dormitory['d_Gender'] == 0) ? 'selected' : ''; ?>>Female Only</option>
                        </select>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="d_Availability" name="d_Availability" value="1" <?= (isset($dormitory['d_Availability']) && $dormitory['d_Availability'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="d_Availability">Available</label>
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

    var customIcon = L.icon({
        iconUrl: './assets/images/marker-icon-2x-red.png', // Path to your custom marker image
        iconSize: [25, 41], // Size of the icon (width, height)
        iconAnchor: [15, 41], // The point of the icon which will correspond to marker's location (x, y) - center bottom of the icon
        popupAnchor: [-2, -41], // The point from which the popup should open relative to the iconAnchor (x, y) - directly above the marker
    });

    // Add tile layer
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    // Add marker and display dorm name
    L.marker([<?php echo htmlspecialchars($latitude); ?>, <?php echo htmlspecialchars($longitude); ?>], {
            icon: customIcon
        }).addTo(map)
        .bindPopup('<?php echo $dormName; ?>')
        .openPopup();

    $(document).ready(function() {
        // Date Calendar
        // Get the current date and time
        const now = new Date();
        const formattedDateTime = now.toISOString().slice(0, 16); // Format to YYYY-MM-DDTHH:MM

        // Set the min attribute for the datetime-local input
        $('#visitDateTime').attr('min', formattedDateTime);

        // Image Modal
        // Attach click event to all elements with class 'open-modal'
        $('.open-modal').on('click', function(e) {
            e.preventDefault(); // Prevent the default behavior of the anchor tag

            // Get the index from the data-index attribute
            var index = $(this).data('index');

            // Show the modal
            $('#imageCarouselModal').modal('show');

            // Move the carousel to the corresponding slide
            var $carousel = $('#modalCarousel');
            $carousel.carousel(index); // Move to the specific slide based on the index
        });

        $('#toggle-description').click(function(event) {
            event.preventDefault(); // Prevent the default anchor click behavior

            // Toggle the full description
            $('#full-description').collapse('toggle');

            // Change the button text based on the current state
            const isExpanded = $(this).attr('aria-expanded') === 'true';
            $(this).attr('aria-expanded', !isExpanded); // Update aria-expanded attribute
            $(this).text(isExpanded ? 'View Less' : 'View More'); // Change button text
        });
    });
</script>