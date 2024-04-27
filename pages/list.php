<?php
// inaccessible for students
if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 1) {
    header('Location: ?page=index');
    die();
}

// submit
if (isset($_POST['submit'])) {
    // Sanitize input
    $d_Name = mysqli_real_escape_string($conn, $_POST['d_Name']);
    $d_Address = mysqli_real_escape_string($conn, $_POST['d_Address']);
    $d_Type = mysqli_real_escape_string($conn, $_POST['d_Type']);
    $d_Capacity = mysqli_real_escape_string($conn, $_POST['d_Capacity']);
    $d_Price = mysqli_real_escape_string($conn, $_POST['d_Rent']);
    $d_Desc = mysqli_real_escape_string($conn, $_POST['d_Desc']);

    // File Upload
    $uploadDir = 'uploads/'; // Directory where uploaded files will be saved
    $uploadedFiles = array();
    foreach ($_FILES['upload']['name'] as $key => $filename) { // wala pa yung pag filter ng size, let's say, limit natin maximum eh 25 mb yung file size
        $tmp_name = $_FILES['upload']['tmp_name'][$key];
        $targetFile = $uploadDir . basename($filename);
        if (move_uploaded_file($tmp_name, $targetFile)) {
            $uploadedFiles[] = $targetFile;
        } else {
            echo "Error uploading file " . $filename . "<br>";
        }
    }

    // Insert data into the database
    $sql = "INSERT INTO your_table_name (d_Name, d_Address, d_Type, d_Capacity, d_Price, d_Desc, file_path) VALUES ('$d_Name', '$d_Address', '$d_Type', '$d_Capacity', '$d_Price', '$d_Desc', '" . implode(",", $uploadedFiles) . "')";

    if (mysqli_query($conn, $sql)) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

?>

<style>
    #dropArea {
        border: 2px dashed #ccc;
        padding: 20px;
        text-align: center;
    }

    #dropArea.highlight {
        border-color: #007bff;
    }
</style>
<!-- top section -->
<section class="p-3 p-md-4 p-xl-5">
    <div class="container p-xl-5" style="margin-top: 100px;">
        <div class="row">
            <div class="col-12 col-md-8 offset-md-2 col-xl-6 offset-xl-1">
                <h1 class="fw-bold">List your property</h1>
                <p class="text-left lead">Listing your dormitory space online for rent has never been simpler. If you're eager to find tenants for your dormitory, just share some details about your space and yourself, and we'll connect you with genuine renters offering the best rates in the market.</p>
                <a href="#list-property" class="btn btn-dark btn-lg">Get Started</a>
            </div>
        </div>
    </div>
</section>

<!-- form section -->
<section class="p-3 p-md-4 p-xl-5 bg-light-subtle">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-11" id="list-property">
                <h2 class="h2 mb-4">Tell us about your property!</h2>
                <div class="card border-light-subtle shadow-sm">
                    <div class="row g-0">
                        <div class="col-12">
                            <div class="card-body p-3 p-md-4 p-xl-5">
                                <form action="" class="row g-3" enctype="multipart/form-data" method="post">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="d_Name" class="form-control" id="floatingInput" placeholder="Dorm Name" required>
                                            <label for="floatingInput">Dorm Name</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="d_Address" class="form-control" id="d_Address" placeholder="Address" required>
                                            <label for="d_Address">Address</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <select id="d_Type" name="d_Type" class="form-select" required>
                                            <option selected disabled>Dorm Type</option>
                                            <option value="single">Single</option>
                                            <option value="shared">Shared</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" name="d_Capacity" class="form-control" id="d_Capacity" placeholder="Capacity" required>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="d_Rent" name="d_Rent" placeholder="Rent Price" min="500" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="d_Desc" class="form-label">Property Description</label>
                                        <textarea class="form-control" name="d_Desc" id="d_Desc" rows="5" required></textarea>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-file" id="dropArea">
                                            <label class="form-label">Drag and drop images here or click to upload</label>
                                            <input type="file" name="upload" class="form-control" name="propertyImages[]" accept=".png, .jpg, .gif" multiple required>
                                        </div>
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dropArea = document.getElementById('dropArea');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropArea.classList.add('highlight');
        }

        function unhighlight() {
            dropArea.classList.remove('highlight');
        }

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            handleFiles(files);
        }

        const fileInput = document.getElementById('fileInput');

        fileInput.addEventListener('change', () => {
            const files = fileInput.files;
            handleFiles(files);
        });

        function handleFiles(files) {
            for (let i = 0; i < files.length; i++) {
                // Here you can do something with each file, like displaying their names or sizes.
                console.log(files[i].name);
            }
        }
    });
</script>