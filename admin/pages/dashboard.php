<?php
if (!isset($_SESSION["a_ID"])) {
    header("location:login");
    exit();
}

$a_Username = $_SESSION['a_Username'];

// Query to count registered owners and tenants
$ownersCount = 0;
$tenantsCount = 0;
$pendingCount = 0;
$dormitoriesActiveCount = 0;
$dormitoriesInactiveCount = 0;

// Count registered owners (assuming u_Account_Type = 0 indicates owner)
$ownersQuery = "SELECT COUNT(*) as count FROM user WHERE u_Account_Type = 0";
$result = $con->query($ownersQuery);
if ($result) {
    $row = $result->fetch_assoc();
    $ownersCount = $row['count'];
}

// Count registered tenants (assuming u_Account_Type = 1 indicates tenant)
$tenantsQuery = "SELECT COUNT(*) as count FROM user WHERE u_Account_Type = 1";
$result = $con->query($tenantsQuery);
if ($result) {
    $row = $result->fetch_assoc();
    $tenantsCount = $row['count'];
}

// Count pending owners (assuming u_Account_Type = 2 indicates pending)
$pendingQuery = "SELECT COUNT(*) as count FROM user WHERE u_Account_Type = 2";
$result = $con->query($pendingQuery);
if ($result) {
    $row = $result->fetch_assoc();
    $pendingCount = $row['count'];
}

// Count dormitories with d_RegistrationStatus = 1
$dormitoriesActiveQuery = "SELECT COUNT(*) as count FROM dormitory WHERE d_RegistrationStatus = 1";
$result = $con->query($dormitoriesActiveQuery);
if ($result) {
    $row = $result->fetch_assoc();
    $dormitoriesActiveCount = $row['count'];
}

// Count dormitories with d_RegistrationStatus = 0
$dormitoriesInactiveQuery = "SELECT COUNT(*) as count FROM dormitory WHERE d_RegistrationStatus = 0";
$result = $con->query($dormitoriesInactiveQuery);
if ($result) {
    $row = $result->fetch_assoc();
    $dormitoriesInactiveCount = $row['count'];
}

// Count rooms with r_RegistrationStatus = 1 (active)
$roomsActiveQuery = "SELECT COUNT(*) as count FROM room WHERE r_RegistrationStatus = 1";
$result = $con->query($roomsActiveQuery);
if ($result) {
    $row = $result->fetch_assoc();
    $roomsActiveCount = $row['count'];
}

// Count rooms with r_RegistrationStatus = 0 (inactive)
$roomsInactiveQuery = "SELECT COUNT(*) as count FROM room WHERE r_RegistrationStatus = 0";
$result = $con->query($roomsInactiveQuery);
if ($result) {
    $row = $result->fetch_assoc();
    $roomsInactiveCount = $row['count'];
}

?>

<!-- Top Cards -->
<div class="container pt-5 mt-3">
    <div class="row pt-5">
        <h5>User Management</h5>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <a href="owners-list" class="text-decoration-none text-dark">
                <div class="card shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-person-circle me-3" viewBox="0 0 16 16">
                            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                            <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                        </svg>
                        <div>
                            <h5 class="card-title mb-0"><?php echo $ownersCount; ?></h5>
                            <p class="mb-0 text-muted">Registered Owners</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <a href="tenants-list" class="text-decoration-none text-dark">
                <div class="card shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-people-fill me-3" viewBox="0 0 16 16">
                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5" />
                        </svg>
                        <div>
                            <h5 class="card-title mb-0"><?php echo $tenantsCount; ?></h5>
                            <p class="mb-0 text-muted">Registered Tenants</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <a href="inactive-owners" class="text-decoration-none text-dark">
                <div class="card shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-person-lines-fill me-3" viewBox="0 0 16 16">
                            <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z" />
                        </svg>
                        <div>
                            <h5 class="card-title mb-0"><?php echo $pendingCount; ?></h5>
                            <p class="mb-0 text-muted">Pending Owners</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

    </div>

    <!-- Dorms Count -->
    <div class="row pt-3">
        <h5>Dorm Management</h5>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <a href="active-dorm" class="text-decoration-none text-dark">
                <div class="card shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-house-check-fill me-3" viewBox="0 0 16 16">
                            <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293z" />
                            <path d="m8 3.293 4.712 4.712A4.5 4.5 0 0 0 8.758 15H3.5A1.5 1.5 0 0 1 2 13.5V9.293z" />
                            <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.707l.547.547 1.17-1.951a.5.5 0 1 1 .858.514" />
                        </svg>
                        <div>
                            <h5 class="card-title mb-0"><?php echo $dormitoriesActiveCount; ?></h5>
                            <p class="mb-0 text-muted">Active Dormitories</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <a href="inactive-dorm" class="text-decoration-none text-dark">
                <div class="card shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-house-slash-fill me-3" viewBox="0 0 16 16">
                            <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293z" />
                            <path d="m8 3.293 4.712 4.712A4.5 4.5 0 0 0 8.758 15H3.5A1.5 1.5 0 0 1 2 13.5V9.293z" />
                            <path d="M13.879 10.414a2.5 2.5 0 0 0-3.465 3.465zm.707.707-3.465 3.465a2.501 2.501 0 0 0 3.465-3.465m-4.56-1.096a3.5 3.5 0 1 1 4.949 4.95 3.5 3.5 0 0 1-4.95-4.95Z" />
                        </svg>
                        <div>
                            <h5 class="card-title mb-0"><?php echo $dormitoriesInactiveCount; ?></h5>
                            <p class="mb-0 text-muted">Pending Dormitories</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Room Count -->
    <div class="row pt-3">
        <h5>Room Management</h5>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <a href="active-room" class="text-decoration-none text-dark">
                <div class="card shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-door-open-fill" viewBox="0 0 16 16">
                            <path d="M1.5 15a.5.5 0 0 0 0 1h13a.5.5 0 0 0 0-1H13V2.5A1.5 1.5 0 0 0 11.5 1H11V.5a.5.5 0 0 0-.57-.495l-7 1A.5.5 0 0 0 3 1.5V15zM11 2h.5a.5.5 0 0 1 .5.5V15h-1zm-2.5 8c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1" />
                        </svg>
                        <div>
                            <h5 class="card-title mb-0"><?php echo $roomsActiveCount; ?></h5>
                            <p class="mb-0 text-muted">Active Rooms</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <a href="inactive-room" class="text-decoration-none text-dark">
                <div class="card shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-door-closed-fill" viewBox="0 0 16 16">
                            <path d="M12 1a1 1 0 0 1 1 1v13h1.5a.5.5 0 0 1 0 1h-13a.5.5 0 0 1 0-1H3V2a1 1 0 0 1 1-1zm-2 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
                        </svg>
                        <div>
                            <h5 class="card-title mb-0"><?php echo $roomsInactiveCount; ?></h5>
                            <p class="mb-0 text-muted">Pending Rooms</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>