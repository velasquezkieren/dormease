<?php
// backup sa signup.php
if (isset($_POST['submit'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    // $confirm_password = $_POST['confirm_password'];
    $contact_num = $_POST['contact_num'];
    $account_type = $_POST['account_type'];
    $gender = $_POST['gender'];

    // Check if the email already exists
    $check_query = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($con, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "Email already exists.";
    } else {
        // Insert user data into database
        $insert_query = "INSERT INTO users (firstname, lastname, email, password, contact_num, account_type, gender) VALUES ('$firstname', '$lastname', '$email', '$password', '$contact_num', '$account_type', '$gender')";

        if (mysqli_query($con, $insert_query)) {
            header("location:?page=login");
            // You can redirect the user to a login page or any other page after successful registration
        } else {
            echo "Error: " . $insert_query . "<br>" . mysqli_error($con);
        }
    }
}
?>

<?php
// backup for login.php
if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $query = mysqli_query($con, $sql);
    $row = mysqli_num_rows($query);
    $data = mysqli_fetch_assoc($query);
    if ($row < 1) {
        header("location:?page=login");
        die();
        exit();
    } else {
        $_SESSION['email'] = $email;
        $_SESSION['password'] = $password;
        $_SESSION['firstname'] = $data['firstname'];
        $_SESSION['lastname'] = $data['lastname'];
        $_SESSION['account_type'] = $data['account_type'];
        header("Location:?page=index");
        die();
    }
}

?>


<?php
// login backup 4/24/2024
include('./config.php');

if (isset($_SESSION['email']) && isset($_SESSION['password'])) {
    // Redirect to the feed page or any other appropriate page
    header("Location: index.php?page=feed");
    exit(); // Stop further execution
}

// condition for logging in
if (isset($_POST['submit'])) {
    // email validation
    $email = $_POST['email'];
    $validate_email = filter_var($email, FILTER_VALIDATE_EMAIL);

    // password sanitation
    $pattern_pass = '/.{8,20}/';
    $password = $_POST['password'];
    $result_password = preg_match($pattern_pass, $password);

    // sanitation and validation condition
    if ($validate_email && $result_password == 1) {
        $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
        $query = mysqli_query($con, $sql);
        $row = mysqli_num_rows($query);
        $data = mysqli_fetch_assoc($query);
        if ($row < 1) {
            // wrong credentials redirect back to login page
            header("location:?page=login&not-match");
            die();
            exit();
        } else {
            // correct credentials
            $_SESSION['email'] = $email;
            $_SESSION['password'] = $password;
            $_SESSION['firstname'] = $data['firstname'];
            $_SESSION['lastname'] = $data['lastname'];
            $_SESSION['account_type'] = $data['account_type'];
            header("Location:?page=index");
            die();
        }
    }
}
?>


<!-- back up step2 form -->

<section class="p-3 p-md-4 p-xl-5" style="display:none;" id="secondFormSection">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-11">
                <div class="card border-light-subtle shadow-sm">
                    <div class="row g-0">
                        <div class="col-12">
                            <div class="card-body p-3 p-md-4 p-xl-5">
                                <h2 class="h4 text-center">Describe your property</h2>
                                <form class="row g-3" method="post" enctype="multipart/form-data" id="imageUploadForm">
                                    <div class="col-12">
                                        <label for="propertyDescription" class="form-label">Property Description</label>
                                        <textarea class="form-control" id="propertyDescription" rows="5"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-file" id="dropArea">
                                            <label class="form-label">Drag and drop images here or click to upload</label>
                                            <input type="file" class="form-control" name="propertyImages[]" accept="image/*" multiple>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" name="submit" class="btn btn-primary">Submit</button>
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
    function showNextForm() {
        document.getElementById('firstFormSection').style.display = 'none';
        document.getElementById('secondFormSection').style.display = 'block';
    }
    // Get drop area element
    var dropArea = document.getElementById('dropArea');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropArea.classList.add('highlight');
    }

    function unhighlight(e) {
        dropArea.classList.remove('highlight');
    }

    // Handle dropped files
    dropArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        var dt = e.dataTransfer;
        var files = dt.files;

        handleFiles(files);
    }

    function handleFiles(files) {
        files = [...files];
        files.forEach(uploadFile);
    }

    function uploadFile(file) {
        // Handle file upload here, you can use XMLHttpRequest or fetch API
        // For simplicity, I'm just logging the file name here
        console.log(file.name);
    }
</script>