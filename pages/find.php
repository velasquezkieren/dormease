<?php
if (!isset($_SESSION['u_Email'])) {
?>
    <section class="p-3 p-md-4 p-xl-5">
        <div class="container p-xl-5" style="margin-top: 100px;">
            <div class="row">
                <div class="col-12 col-md-8 offset-md-2 col-xl-6 offset-xl-1">
                    <h1 class="fw-bold">Find your dorm</h1>
                    <p class="text-left lead">Searching for the ideal dormitory space? Look no further! Share your preferences and requirements with us, and we'll connect you with available dorms that match your criteria. Say goodbye to the hassle of hunting for the perfect dorm – let us streamline the process for you!</p>
                    <a href="login" class="btn btn-dark btn-lg">Get Started</a>
                </div>
            </div>
        </div>
    </section>
<?php } else {
?>
    <div class="container">
        <div class="row m-auto">
            <div class="col">
                <div class="input-group mt-5 pt-5 p-4">
                    <input class="form-control" list="datalistOptions" id="exampleDataList" placeholder="Type to search...">
                    <datalist id="datalistOptions">
                        <option value="Cabanatuan">
                        <option value="Sta. Rosa">
                        <option value="Sumacab">
                    </datalist>
                </div>
            </div>
            <div class="col">
                <div class="input-group mt-5 pt-5">
                    <select class="form-select" aria-label="Default select example">
                        <option value="Sort By" selected disabled>Sort By</option>
                        <option value="1">Price - Low to High</option>
                        <option value="2">Price - High to Low</option>
                        <option value="3">Rating - Low to High</option>
                        <option value="4">Rating - High to Low</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row mt-5">
            <div class="col-lg-3 border-0 card mb-2">
                <a href="property"><img src="img/sample.jpg" class="card-img-top" alt="..."></a>
                <div class="card-body">
                    <h5 class="card-title">Cabanatuan</h5>
                    <h6 class="card-text"><i class="bi bi-geo-alt-fill"></i>FX49+J2M, Lungsod ng Cabanatuan, Nueva Ecija</h6>
                    <p class="card-text">Starts at<br>$1,000</p>
                    <?php
                    if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 1) {
                        echo '<a href="property" class="btn btn-dark">View Property</a>';
                    } elseif (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 0) {
                        echo '<a href="property" class="btn btn-dark">Edit Property</a>';
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>

<?php } ?>