<?php
include 'db.php';
include 'header.php';

// Fetch all trainers (no packages here)
$sql = "SELECT * FROM trainers ORDER BY name ASC";
$result = $conn->query($sql);

// Prepare trainers array (optional, you can directly loop over $result)
$trainers = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $trainers[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Trainers | FitZone Fitness Center</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .trainer-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    .trainer-img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #0d6efd;
    }
  </style>
</head>

<body class="bg-light">

<main class="py-5">
  <div class="container">
    <h1 class="text-center text-primary fw-bold mb-5">Meet Our Expert Trainers</h1>

    <div class="row g-4">
      <?php if (!empty($trainers)): ?>
        <?php foreach ($trainers as $trainer): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 trainer-card p-3 border-0 shadow-sm">
              <div class="text-center">
                <img 
                  src="<?= htmlspecialchars($trainer['image']) ?: 'uploads/trainers/default.png' ?>" 
                  alt="<?= htmlspecialchars($trainer['name']) ?>" 
                  onerror="this.onerror=null; this.src='uploads/trainers/default.png';"
                  class="trainer-img mb-3"
                >
                <h5 class="card-title text-primary"><?= htmlspecialchars($trainer['name']) ?></h5>
                <p class="card-text fst-italic text-muted"><?= nl2br(htmlspecialchars($trainer['bio'])) ?></p>
              </div>

              <ul class="list-group list-group-flush small">
                <li class="list-group-item"><strong>Experience:</strong> <?= htmlspecialchars($trainer['experience_years']) ?> years</li>
                <li class="list-group-item"><strong>Certification:</strong> <?= htmlspecialchars($trainer['certification']) ?></li>
              </ul>

              <div class="card-body text-center mt-3">
                <a href="trainer-packages.php?trainer_id=<?= $trainer['id'] ?>" class="btn btn-primary w-100">
                  View Packages
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <p class="text-center text-muted">No trainers found.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php include 'footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
