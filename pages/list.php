<?php
include('config.php');
if (isset($_POST['submit'])) {
    if (!isset($_SESSION['email']) && isset($_SESSION['password'])) {
        header('location:?page=login');
        die();
    }
}
?>

<section class="p-3 p-md-4 p-xl-5">
    <div class="container" style="padding-top:80px;">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-11">
                <div class="card border-light-subtle shadow-sm">
                    <div class="row g-0">
                        <div class="col-12">
                            <div class="card-body p-3 p-md-4 p-xl-5">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-5">
                                            <div class="text-center mb-4">
                                                <a href="">
                                                    <img class="img-fluid rounded-start" src="./img/logo.png" width="30%" height="auto">
                                                </a>
                                            </div>
                                            <h2 class="h4 text-center">Property</h2>
                                        </div>
                                    </div>
                                </div>
                                <form class="row g-3" enctype="multipart/form-data">
                                    <div class="col-12">
                                        <label for="inputEmail" class="form-label">Property Name</label>
                                        <input type="email" class="form-control" id="inputEmail" placeholder="name@example.com">
                                    </div>
                                    <div class="col-12">
                                        <label for="inputAddress" class="form-label">Address</label>
                                        <input type="text" class="form-control" id="inputAddress" placeholder="1234 Main St">
                                    </div>
                                    <div class="col-12">
                                        <label for="inputAddress2" class="form-label">Address 2</label>
                                        <input type="text" class="form-control" id="inputAddress2" placeholder="Apartment, studio, or floor">
                                    </div>
                                    <div class="col-12">
                                        <label for="inputCity" class="form-label">City</label>
                                        <input type="text" class="form-control" id="inputCity">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="inputState" class="form-label">State</label>
                                        <select id="inputState" class="form-select">
                                            <option selected>Choose...</option>
                                            <option>...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="inputZip" class="form-label">Zip</label>
                                        <input type="text" class="form-control" id="inputZip">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-file">
                                            <label class="form-label">Dorm Image</label>
                                            <input type="file" class="form-control" name="profile_picture" accept="image/*">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Submit</button>
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