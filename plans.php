<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$result = mysqli_query($conn, "SELECT * FROM membership_plans WHERE status = 'Active' ORDER BY monthly_price ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Membership Plans</title>
    <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Available Membership Plans</h2>
    <div class="row">
    <?php while ($plan = mysqli_fetch_assoc($result)) { ?>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($plan['name']) ?></h5>
                    <p class="card-text"><?= nl2br(htmlspecialchars($plan['features'])) ?></p>
                    <ul class="list-group mb-3">
                        <li class="list-group-item">Monthly: Rs. <?= htmlspecialchars($plan['monthly_price']) ?></li>
                        <li class="list-group-item">Quarterly: Rs. <?= htmlspecialchars($plan['quarterly_price']) ?></li>
                        <li class="list-group-item">Yearly: Rs. <?= htmlspecialchars($plan['yearly_price']) ?> (<?= htmlspecialchars($plan['yearly_discount']) ?> off)</li>
                    </ul>
                    <div>
                        <a href="subscribe.php?plan_id=<?= $plan['id'] ?>&type=Monthly" class="btn btn-primary btn-sm">Monthly</a>
                        <a href="subscribe.php?plan_id=<?= $plan['id'] ?>&type=Quarterly" class="btn btn-warning btn-sm">Quarterly</a>
                        <a href="subscribe.php?plan_id=<?= $plan['id'] ?>&type=Yearly" class="btn btn-success btn-sm">Yearly</a>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
