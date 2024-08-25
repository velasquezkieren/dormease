<?php
// Inaccessible for students
if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 1) {
    header('Location: home');
    exit();
}

// Handle form submission
if (isset($_POST['submit'])) {
    // Sanitize and validate inputs
    $d_Name = filter_var(trim($_POST['d_Name']), FILTER_SANITIZE_STRING);
    $d_Street = filter_var(trim($_POST['d_Street']), FILTER_SANITIZE_STRING);
    $d_City = filter_var(trim($_POST['d_City']), FILTER_SANITIZE_STRING);
    $d_ZIPCode = filter_var(trim($_POST['d_ZIPCode']), FILTER_SANITIZE_NUMBER_INT);
    $d_Province = filter_var(trim($_POST['d_Province']), FILTER_SANITIZE_STRING);
    $d_Region = filter_var(trim($_POST['d_Region']), FILTER_SANITIZE_STRING);
    $d_Description = filter_var(trim($_POST['d_Description']), FILTER_SANITIZE_STRING);

    // Basic validation
    if (empty($d_Name) || empty($d_Street) || empty($d_City) || empty($d_ZIPCode) || empty($d_Province) || empty($d_Region) || empty($d_Description)) {
        echo "<div class='alert alert-danger'>Please fill in all fields.</div>";
        exit();
    }

    if (!filter_var($d_ZIPCode, FILTER_VALIDATE_INT)) {
        echo "<div class='alert alert-danger'>Invalid ZIP Code.</div>";
        exit();
    }

    // Generate a unique ID for the dormitory
    $d_ID = uniqid('d_');
    $d_Availability = 'Available';  // Default to 'Available'
    $d_Owner = $_SESSION['u_ID'];  // Assuming the user ID is stored in session

    // Prepare and execute the SQL statement
    $stmt = $con->prepare("INSERT INTO dormitory (d_ID, d_Name, d_Street, d_City, d_ZIPCode, d_Province, d_Region, d_Availability, d_Description, d_Owner) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssisssss', $d_ID, $d_Name, $d_Street, $d_City, $d_ZIPCode, $d_Province, $d_Region, $d_Availability, $d_Description, $d_Owner);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Property listed successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
    $con->close();
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