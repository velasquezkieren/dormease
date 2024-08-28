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
?>

<div class="container">
    <div class="row pt-5 text-center text-md-start">
        <div class="col-12 col-md pt-5 d-flex flex-column align-items-center align-items-md-start">
            <p class="h1"><?php echo htmlspecialchars($dormitory['d_Name']); ?></p>
            <div class="d-flex align-items-center">
                <i class="bi bi-geo-alt-fill"></i>
                <p class="h5 mb-0 ms-2"><?php echo htmlspecialchars($dormitory['d_Street'] . ', ' . $dormitory['d_City']); ?></p>
            </div>
        </div>
        <div class="col-12 col-md-auto pt-3 pt-md-5 d-flex justify-content-md-end justify-content-center align-items-center align-items-md-end">
            <?php
            if ($loggedInUserID == $d_Owner_ID && isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 0) {
                echo '<a class="login-button" href="list.php?edit&d_ID=' . urlencode($d_ID) . '">Edit listing</a>';
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
                        echo '<img src="./upload/' . htmlspecialchars($image) . '" class="d-block w-100" alt="Dormitory Image">';
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
            <!-- Google Maps integration here -->
        </div>
    </div>
</div>