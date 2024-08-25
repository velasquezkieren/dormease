<?php
// Inaccessible for students
if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 1) {
    header('Location: home');
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
    <!-- Multi-step form -->
    <div class="container pt-5 mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <!-- Step 1: Dorm Name -->
                <div id="step1" class="form-step">
                    <h2 class="text-center mb-1 pt-3">Dorm Name</h2>
                    <p class="text-center mb-4">What is the name of your property?</p>
                    <form id="multiStepFormStep1">
                        <div class="form-floating mb-3">
                            <input type="text" name="d_Name" class="form-control" id="d_Name" placeholder="Dorm Name" required>
                            <label for="d_Name">Dorm Name</label>
                        </div>
                        <button type="button" id="nextToStep2" class="btn btn-primary w-100">Next</button>
                    </form>
                </div>

                <!-- Step 2: Location -->
                <div id="step2" class="form-step d-none">
                    <h2 class="text-center mb-1 pt-3">Location</h2>
                    <p class="text-center mb-4">Where is your property located?</p>
                    <form id="multiStepFormStep2">
                        <div class="form-floating mb-3">
                            <input type="text" name="d_Street" class="form-control" id="d_Street" placeholder="Street" required>
                            <label for="d_Street">Street</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" name="d_City" class="form-control" id="d_City" placeholder="City" required>
                            <label for="d_City">City</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="number" name="d_ZIPCode" class="form-control" id="d_ZIPCode" placeholder="Zip Code" required>
                            <label for="d_ZIPCode">Zip Code</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" name="d_Province" class="form-control" id="d_Province" placeholder="Province" required>
                            <label for="d_Province">Province</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" name="d_Region" class="form-control" id="d_Region" placeholder="Region" required>
                            <label for="d_Region">Region</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" name="d_Description" class="form-control" id="d_Description" placeholder="Description" required>
                            <label for="d_Description">Description</label>
                        </div>
                        <button type="button" id="backToStep1" class="btn btn-secondary w-100">Back</button>
                        <button type="button" id="nextToStep3" class="btn btn-primary w-100">Next</button>
                    </form>
                </div>

                <!-- Step 3: Image Upload -->
                <!-- Step 3: Image Upload -->
                <div id="step3" class="form-step d-none">
                    <h2 class="text-center mb-1 pt-3">Image Upload</h2>
                    <p class="text-center mb-4">Please upload at least 3 images of your property.</p>
                    <form id="multiStepFormStep3" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple required>
                            <small class="form-text text-muted">Upload a minimum of 3 images and a maximum of 5 images. Each image should be less than 5MB.</small>
                        </div>
                        <button type="button" id="backToStep2" class="btn btn-secondary w-100">Back</button>
                        <button type="submit" id="submitForm" class="btn btn-primary w-100">Submit</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            const step1 = $('#step1');
            const step2 = $('#step2');
            const step3 = $('#step3');

            $('#nextToStep2').on('click', function() {
                // Perform validation for Step 1
                if ($('#multiStepFormStep1')[0].checkValidity()) {
                    step1.addClass('d-none');
                    step2.removeClass('d-none');
                } else {
                    $('#multiStepFormStep1')[0].reportValidity();
                }
            });

            $('#backToStep1').on('click', function() {
                step2.addClass('d-none');
                step1.removeClass('d-none');
            });

            $('#nextToStep3').on('click', function() {
                // Perform validation for Step 2
                if ($('#multiStepFormStep2')[0].checkValidity()) {
                    step2.addClass('d-none');
                    step3.removeClass('d-none');
                } else {
                    $('#multiStepFormStep2')[0].reportValidity();
                }
            });

            $('#backToStep2').on('click', function() {
                step3.addClass('d-none');
                step2.removeClass('d-none');
            });

            $('#multiStepFormStep3').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: 'process_form.php', // Your PHP file to handle the form submission
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        // Handle successful form submission
                        alert('Form submitted successfully!');
                        // Optionally redirect or clear form
                        // window.location.href = 'thank_you_page.html';
                        $('#multiStepFormStep3')[0].reset();
                        step3.addClass('d-none');
                        step1.removeClass('d-none');
                    },
                    error: function(xhr, status, error) {
                        // Handle errors
                        alert('An error occurred: ' + error);
                    }
                });
            });
        });
    </script>
<?php
} ?>