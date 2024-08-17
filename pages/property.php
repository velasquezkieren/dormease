<div class="container">
    <div class="row pt-5 text-center text-md-start">
        <div class="col-12 col-md pt-5 d-flex flex-column align-items-center align-items-md-start">
            <p class="h1">Wood Dorm</p>
            <div class="d-flex align-items-center">
                <i class="bi bi-geo-alt-fill"></i>
                <p class="h5 mb-0 ms-2">Sumacab Este, Cabanatuan City</p>
            </div>
        </div>
        <div class="col-12 col-md-auto pt-3 pt-md-5 d-flex justify-content-md-end justify-content-center align-items-center align-items-md-end">
            <?php
            if ((isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 0)) {
                echo '<a class="login-button" href="list&edit">Edit listing</a>';
            } elseif ((isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] == 1)) {
                echo '<a class="login-button" href="list">Book now 2,000/month</a>';
            }
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-md pt-5 d-flex justify-content-center">
            <img src="./img/sample.jpg" alt="Sample Image" class="img-fluid w-100 h-auto">
        </div>
    </div>
    <div class="row pt-5">
        <p class="h1">About</p>
        <div class="col-12 col-md">
            <p class="d-flex justify-content">Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam voluptatum rem quam earum pariatur accusantium non commodi dolorum, minus illo animi soluta aperiam itaque ex maiores molestias fugit adipisci necessitatibus?
                Assumenda quisquam possimus tenetur dolor accusantium delectus nihil voluptas dolorum sunt quasi, recusandae autem iusto esse dolorem impedit est reiciendis ut debitis exercitationem tempora numquam corporis. Fugiat necessitatibus laborum doloremque!
                Suscipit officia aspernatur aut error ipsam tempora neque aliquam? Nihil nam deleniti nostrum molestiae pariatur veniam maxime et ipsam fugiat, impedit numquam nesciunt consequatur facilis quaerat! Sit et sapiente cumque?</p>
        </div>
        <div class="col-12">
            <!-- dito ilalagay yung Google Maps -->
        </div>
    </div>

    <div class="row">

    </div>
</div>