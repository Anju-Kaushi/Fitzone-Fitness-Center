<?php
include("header.php");
include("db.php");

$team = [];
$sql = "SELECT name, bio, experience_years, certification, image FROM trainers";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $team[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>About Us | FitZone Fitness Center</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />

  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #fff;
      color: #333;
    }

    .team-img {
      width: 140px;
      height: 140px;
      object-fit: cover;
      border: 4px solid #28a745;
    }
  </style>
</head>

<body>
  <main class="container py-5">

    <!-- Hero Welcome Section (from Homepage) -->
    <section class="text-center mb-5">
      <h1 class="display-5 fw-bold text-success">Welcome to FitZone Fitness Center</h1>
      <p class="lead mx-auto" style="max-width: 720px;">
        Located in Kurunegala, FitZone empowers your journey with modern facilities, passionate trainers, and a vibrant fitness community.
      </p>
    </section>

    <!-- About Us Introduction (from Homepage) -->
    <section class="row align-items-center gy-4 mb-5">
      <div class="col-md-6">
        <img src="Images/About Us.jpg" alt="About FitZone" class="rounded shadow w-100" />
      </div>
      <div class="col-md-6">
        <p class="mb-3">
          At <strong>FitZone Fitness Center</strong>, we believe fitness is more than just workouts — it’s a lifestyle transformation. Based in Kurunegala, our mission is to help you become the strongest, healthiest version of yourself through expert coaching, cutting-edge facilities, and a community that lifts each other higher.
        </p>
        <p class="mb-3">
          Whether you're stepping into the gym for the first time or looking to take your training to the next level, FitZone is where your journey begins. We don’t just train bodies — we build confidence, resilience, and results that last.
        </p>
        <ul class="ps-3">
          <li>Certified, experienced personal trainers and group instructors</li>
          <li>Custom training programs tailored to your goals</li>
          <li>Nutrition and wellness coaching</li>
          <li>Supportive environment for all fitness levels</li>
        </ul>
      </div>
    </section>

    <!-- Mission & Values -->
    <section class="row mb-5">
      <div class="col-md-6">
        <h2 class="h4 fw-bold mb-3">Our Mission</h2>
        <p>
          To help people transform their lives through fitness, discipline, and sustainable lifestyle changes. We’re committed to delivering top-tier training, cutting-edge equipment, and a positive environment that motivates every member.
        </p>
      </div>
      <div class="col-md-6">
        <h2 class="h4 fw-bold mb-3">Our Core Values</h2>
        <ul class="list-unstyled">
          <li>✅ Integrity in every interaction</li>
          <li>✅ Commitment to excellence</li>
          <li>✅ Respect and inclusivity for all</li>
          <li>✅ Encouragement through community</li>
        </ul>
      </div>
    </section>

    <!-- History -->
    <section class="mb-5">
      <h2 class="h4 fw-bold mb-3">Our Story</h2>
      <p>
        Established in 2020 in Kurunegala, FitZone began as a humble local gym with a big dream — to become the most trusted name in fitness in the region. Over the years, we've expanded our facilities, introduced innovative fitness programs, and built a passionate community that keeps growing stronger.
      </p>
    </section>

    <!-- Team Section -->
    <section class="text-center mb-5">
      <h2 class="h4 fw-bold mb-4">Meet Our Team</h2>
      <div class="row">
        <?php if (!empty($team)): ?>
          <?php foreach ($team as $member): ?>
            <div class="col-md-4 mb-4">
              <div class="card shadow-sm h-100">
                <div class="card-body">
                  <img src="<?= htmlspecialchars($member['image']) ?>" alt="<?= htmlspecialchars($member['name']) ?>" class="rounded-circle team-img mb-3" />
                  <h5 class="fw-semibold"><?= htmlspecialchars($member['name']) ?></h5>
                  <p class="text-success mb-1"><strong>Certification:</strong> <?= htmlspecialchars($member['certification']) ?></p>
                  <p class="mb-1"><strong>Experience:</strong> <?= (int)$member['experience_years'] ?> years</p>
                  <p class="text-muted small"><?= htmlspecialchars($member['bio']) ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-muted">No team members available at the moment.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- Facilities -->
    <section class="mb-5">
      <h2 class="h4 fw-bold mb-3">Our Facilities</h2>
      <p class="mb-4">
        FitZone is equipped with premium-grade cardio machines, strength stations, and functional training zones. We offer a clean, spacious environment that fosters focus, energy, and progression — plus a dedicated area for group classes and personal coaching.
      </p>
      <div class="row g-4">
        <div class="col-md-6">
          <img src="Images/FitZone gym equipment area.png" class="img-fluid rounded shadow-sm" alt="Gym Equipment Area" />
        </div>
        <div class="col-md-6">
          <img src="Images/FitZone group class studio.png" class="img-fluid rounded shadow-sm" alt="Group Class Studio" />
        </div>
      </div>
    </section>

  </main>

  <?php include("footer.php"); ?>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
