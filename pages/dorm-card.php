<?php
$owner_name = htmlspecialchars($dorm['u_FName'] . ' ' . $dorm['u_MName'] . ' ' . $dorm['u_LName']);
$images = explode(',', $dorm['d_PicName']);
$first_image = $images[0];
$description = substr($dorm['d_Description'], 0, 100);
if (strlen($dorm['d_Description']) > 100) {
    $description .= '...';
}
?>

<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
    <a href="property?d_ID=<?= urlencode($dorm['d_ID']); ?>" class="text-decoration-none">
        <div class="card h-100 border-1">
            <!-- Carousel -->
            <div id="carousel-<?= $dorm['d_ID']; ?>" class="carousel slide card-img-container" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : ''; ?>">
                            <img src="upload/<?= htmlspecialchars($dorm['d_ID'] . '/' . $image); ?>" class="d-block w-100 img-fluid" loading="lazy" alt="<?= htmlspecialchars($dorm['d_Name']); ?>" style="height: 200px; object-fit: cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?= $dorm['d_ID']; ?>" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?= $dorm['d_ID']; ?>" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
            <!-- End Carousel -->

            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?= htmlspecialchars($dorm['d_Name']); ?></h5>
                <p class="card-text text-truncate" style="max-height: 3.6em; overflow: hidden;"><?= htmlspecialchars($description); ?></p>
                <p class="card-text"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($dorm['d_Street']) . ', ' . htmlspecialchars($dorm['d_City']); ?></p>
                <p class="card-text"><strong>Owner:</strong> <?= $owner_name; ?></p>
                <span class="btn btn-dark mt-auto">View Details</span>
            </div>
        </div>
    </a>
</div>