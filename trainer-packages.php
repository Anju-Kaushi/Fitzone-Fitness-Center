<?php
include 'db.php';
include 'header.php';

$trainer_id = intval($_GET['trainer_id'] ?? 0);

// Fetch trainer info
$stmtTrainer = $conn->prepare("SELECT * FROM trainers WHERE id = ?");
$stmtTrainer->bind_param("i", $trainer_id);
$stmtTrainer->execute();
$trainer = $stmtTrainer->get_result()->fetch_assoc();

// Fetch packages
$stmtPackages = $conn->prepare("SELECT * FROM packages WHERE trainer_id = ?");
$stmtPackages->bind_param("i", $trainer_id);
$stmtPackages->execute();
$packages = $stmtPackages->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $trainer ? htmlspecialchars($trainer['name']) . ' - Packages' : 'Trainer Not Found' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .trainer-card {
      background: #fff;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }
    .package-card {
      transition: all 0.3s ease;
      border-radius: 15px;
    }
    .package-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .badge-pill {
      font-size: 0.85rem;
      padding: 5px 12px;
      border-radius: 50px;
    }
  </style>
</head>
<body class="bg-light">

<div class="container py-5">
  <!-- Back to Trainers button -->
  <div class="mb-4">
    <a href="trainers.php" class="btn btn-outline-secondary">&larr; Back to Trainers</a>
  </div>

  <?php if ($trainer): ?>
    <div class="trainer-card text-center mb-5">
      <img 
        src="<?= htmlspecialchars($trainer['image']) ?>" 
        class="rounded-circle border border-success shadow-sm" 
        style="width: 130px; height: 130px; object-fit: cover;"
        alt="<?= htmlspecialchars($trainer['name']) ?>"
      >
      <h2 class="text-dark mt-3"><?= htmlspecialchars($trainer['name']) ?></h2>
      <p class="text-muted mt-2 mb-1"><?= nl2br(htmlspecialchars($trainer['bio'])) ?></p>
      <div class="mt-3">
        <span class="badge bg-success text-white me-2 badge-pill">Experience: <?= $trainer['experience_years'] ?> Years</span>
        <span class="badge bg-primary text-white badge-pill"><?= htmlspecialchars($trainer['certification']) ?></span>
      </div>
    </div>

    <h4 class="mb-4 text-dark text-center">Available Personal Training Packages</h4>
    <div class="row">
      <?php if ($packages->num_rows > 0): ?>
        <?php while ($pkg = $packages->fetch_assoc()): ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="card package-card border-0 shadow-sm h-100">
              <div class="card-body">
                <h5 class="card-title text-success fw-bold">💪 <?= $pkg['sessions'] ?> Sessions</h5>
                <p class="card-text mb-2"><strong>Price:</strong> <span class="text-primary">LKR <?= number_format($pkg['price'], 2) ?></span></p>
                <p class="card-text text-secondary"><?= htmlspecialchars($pkg['description']) ?></p>
              </div>
              <div class="card-footer bg-white border-top-0 text-center">
                <a href="Contact.php" class="btn btn-outline-success w-100">Contact Now</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-muted text-center">No packages found for this trainer.</p>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-danger text-center">Trainer not found.</div>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
