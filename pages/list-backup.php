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
    $d_Name = $_POST['d_Name'];
    $d_Street = $_POST['d_Street'];
    $d_City = $_POST['d_City'];
    $d_ZIPCode = $_POST['d_ZIPCode'];
    $d_Province = $_POST['d_Province'];
    $d_Region = $_POST['d_Region'];
    $d_Description = $_POST['d_Description'];
    $d_Availability = '1';  // Default availability
    $d_Owner = $_SESSION['u_ID'];  // Assuming the user ID is stored in session

    // Handle multiple image uploads
    $imageNames = [];
    foreach ($_FILES['d_PicName']['tmp_name'] as $key => $tmp_name) {
        $file_name = $_FILES['d_PicName']['name'][$key];
        $file_tmp = $_FILES['d_PicName']['tmp_name'][$key];

        // Generate a unique name for the image to avoid overwriting
        $uniqueFileName = uniqid() . '@dormease@' . $file_name;

        // Move the uploaded file to the desired directory
        move_uploaded_file($file_tmp, "upload/" . $uniqueFileName);

        // Collect the image name
        $imageNames[] = $uniqueFileName;
    }

    // Convert image names array to a comma-separated string
    $d_PicNames = implode(',', $imageNames);

    // Insert dormitory details into the database
    $sql = "INSERT INTO dormitory (d_ID, d_Name, d_Street, d_City, d_ZIPCode, d_Province, d_Region, d_Availability, d_Description, d_Owner, d_PicName)
            VALUES ('$d_ID', '$d_Name', '$d_Street', '$d_City', '$d_ZIPCode', '$d_Province', '$d_Region', '$d_Availability', '$d_Description', '$d_Owner', '$d_PicNames')";

    if (mysqli_query($con, $sql)) {
        echo "<script>alert(`Dormitory listed successfully!`);</script>";
    } else {
        echo "<script>(Error: " . mysqli_error($con) . "); </script>";
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
    <!-- form section -->
    <section class="p-3 p-md-4 p-xl-5 bg-light-subtle">
        <div class="container" style="margin-top:100px;">
            <div class="row justify-content-center">
                <div class="col-12 col-xxl-11" id="list-property">
                    <?php
                    if (isset($_GET['edit'])) {
                        echo '<h2 class="h2 mb-4">Edit Property</h2>';
                    } else {
                        echo '<h2 class="h2 mb-4">Tell us about your property!</h2>';
                    }
                    ?>
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
                                        <div class="col-12">
                                            <label for="d_Description" class="form-label">Property Description</label>
                                            <textarea class="form-control" name="d_Description" id="d_Description" rows="5" required></textarea>
                                        </div>
                                        <div class="col-12">
                                            <input type="file" name="d_PicName[]" accept="image/*" multiple required>
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
<?php
}
?>