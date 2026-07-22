<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);

// Fetch trainers securely
$trainers = [];
if ($stmt = $conn->prepare("SELECT id, name, certification, image FROM trainers ORDER BY id ASC")) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $trainers[] = $row;
    }
    $stmt->close();
}

// Fetch blog posts securely
$blog_posts = [];
if ($stmt = $conn->prepare("SELECT id, title, content, image FROM blog_posts ORDER BY created_at DESC LIMIT 3")) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $blog_posts[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FitZone Fitness Center</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <style>
    html {
      scroll-behavior: smooth;
    }
    body {
      font-family: 'Roboto', sans-serif;
    }
    .animate-fade-in-up {
      animation: fadeInUp 0.8s ease-out forwards;
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .hero-overlay {
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background: linear-gradient(90deg, rgba(0,0,0,1) 0%, rgba(0,0,0,0.6) 60%, rgba(0,0,0,0) 100%);
      z-index: 1;
    }
  </style>
</head>

<body class="bg-white text-dark">
  <?php include("header.php"); ?>

  <main role="main">
    <!-- Hero Section -->
    <div class="position-relative" style="height:100vh; background: url('Images/Background Image.jpg') center center/cover no-repeat;">
      <div class="hero-overlay"></div>
      <div class="position-relative d-flex flex-column justify-content-center align-items-center text-center text-white h-100 px-3 animate-fade-in-up" style="z-index:2;">
        <h1 class="display-1 fw-bold lh-1">Unleash Your Potential<br><span class="text-success">at FitZone</span></h1>
        <p class="lead mx-auto" style="max-width: 700px;">Achieve your fitness goals with expert guidance, modern equipment, and an empowering community.</p>
        <div class="mt-4">
          <?php if (!$isLoggedIn): ?>
            <a href="Registration.php" class="btn btn-primary btn-lg me-3 shadow-sm">Register</a>
            <a href="Login.php" class="btn btn-success btn-lg shadow-sm">Login</a>
          <?php else: ?>
            <a href="customer-dashboard.php" class="btn btn-success btn-lg shadow-sm">Dashboard</a>
            <a href="logout.php" class="btn btn-outline-light btn-lg ms-3 shadow-sm">Logout</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Intro Section -->
    <section class="py-5 bg-white text-center px-3">
      <div class="container">
        <h2 class="display-5 fw-bold mb-3">Welcome to FitZone Fitness Center</h2>
        <p class="text-secondary fs-5">Located in Kurunegala, FitZone empowers your journey with modern facilities, passionate trainers, and a vibrant fitness community.</p>
      </div>
    </section>

    <!-- Quick Access -->
    <section class="py-5 bg-light">
      <div class="container">
        <h2 class="fw-bold text-center mb-5">Quick Access</h2>
        <div class="row g-4">
          <?php
          $quickLinks = [
            ["icon" => "schedule", "title" => "Class Schedules", "desc" => "Weekly group training & fitness timetables.", "link" => "Schedule.php"],
            ["icon" => "fitness_center", "title" => "Membership Plans", "desc" => "Affordable, flexible membership options.", "link" => "Membership.php"],
            ["icon" => "contact_support", "title" => "Contact Info", "desc" => "Reach out for assistance & inquiries.", "link" => "Contact.php"],
          ];
          foreach ($quickLinks as $q): ?>
            <div class="col-md-4">
              <div class="card shadow-sm h-100 text-center">
                <div class="card-body">
                  <span class="material-icons text-success display-4 mb-3"><?= htmlspecialchars($q['icon']) ?></span>
                  <h3 class="card-title h5 fw-semibold mb-2"><?= htmlspecialchars($q['title']) ?></h3>
                  <p class="card-text text-secondary mb-3"><?= htmlspecialchars($q['desc']) ?></p>
                  <a href="<?= htmlspecialchars($q['link']) ?>" class="text-success fw-medium text-decoration-none">Explore</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- About Us -->
    <section class="py-5 bg-white">
      <div class="container">
        <h2 class="fw-bold text-center mb-5">About Us</h2>
        <div class="row align-items-center gy-4">
          <div class="col-md-6">
            <img src="Images/About Us.jpg" alt="About Us" class="rounded shadow w-100" />
          </div>
          <div class="col-md-6">
            <p class="text-secondary mb-3">
              At <strong>FitZone Fitness Center</strong>, we believe fitness is more than just workouts — it’s a lifestyle transformation.
            </p>
            <p class="text-secondary mb-3">
              Whether you're stepping into the gym for the first time or looking to take your training to the next level, FitZone is where your journey begins.
            </p>
            <ul class="mb-4 text-secondary ps-3">
              <li class="mb-2">Certified, experienced personal trainers and group instructors</li>
              <li class="mb-2">Custom training programs tailored to your goals</li>
              <li class="mb-2">Nutrition and wellness coaching</li>
              <li class="mb-2">Supportive environment for all fitness levels</li>
            </ul>
            <a href="Aboutus.php" class="btn btn-success">Read More</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Services -->
    <section class="bg-light py-5">
      <div class="container text-center">
        <h2 class="fw-bold mb-5">Featured Services</h2>
        <div class="row g-4">
          <?php
          $services = [
            ["img" => "Images/State of the art Equipment.jpg", "title" => "Modern Equipment", "desc" => "Access the latest fitness machines and technology."],
            ["img" => "Images/Personalized Training.jpg", "title" => "Personal Training", "desc" => "Customized workouts by certified trainers."],
            ["img" => "Images/Group Classes.jpg", "title" => "Group Classes", "desc" => "Fun and motivating group fitness experiences."],
            ["img" => "Images/Nutrition Counseling.jpeg", "title" => "Nutrition Plans", "desc" => "Expert advice for healthy eating habits."],
          ];
          foreach ($services as $s): ?>
            <div class="col-12 col-md-6 col-lg-3 d-flex">
              <div class="card shadow-sm h-100 w-100">
                <img src="<?= htmlspecialchars($s['img']) ?>" class="card-img-top" alt="<?= htmlspecialchars($s['title']) ?>" style="height:12rem; object-fit:cover;" />
                <div class="card-body d-flex flex-column">
                  <h3 class="card-title h5 text-success"><?= htmlspecialchars($s['title']) ?></h3>
                  <p class="card-text text-secondary flex-grow-1"><?= htmlspecialchars($s['desc']) ?></p>
                  <a href="<?= $isLoggedIn ? 'customer-dashboard.php?page=register-class' : 'Login.php' ?>" class="btn btn-success mt-3 w-100">Book Now</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Trainers -->
    <section class="py-5">
      <div class="container text-center">
        <h2 class="fw-bold mb-5">Meet Our Trainers</h2>
        <div class="row g-4 justify-content-center">
          <?php if (!empty($trainers)): ?>
            <?php foreach ($trainers as $trainer): ?>
              <div class="col-md-4 d-flex">
                <div class="card shadow-sm text-center w-100">
                  <img src="<?= htmlspecialchars($trainer['image'] ?: 'Images/default-trainer.jpg') ?>"
                    alt="<?= htmlspecialchars($trainer['name']) ?>"
                    class="rounded-circle mx-auto mt-4 mb-3 border border-success"
                    style="width: 9rem; height: 9rem; object-fit: cover; border-width: 0.3rem !important;" />
                  <div class="card-body d-flex flex-column">
                    <h3 class="card-title h5"><?= htmlspecialchars($trainer['name']) ?></h3>
                    <p class="fst-italic text-success small mb-3"><?= htmlspecialchars($trainer['certification']) ?></p>
                    <a href="<?= $isLoggedIn ? 'customer-dashboard.php?page=register-class' : 'Login.php' ?>" class="btn btn-success mt-3">Book Now</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-secondary">No trainers available at the moment.</p>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Blog Section -->
    <section class="bg-light py-5">
      <div class="container text-center">
        <h2 class="fw-bold mb-5">Our Blog</h2>
        <div class="row g-4">
          <?php if (!empty($blog_posts)): ?>
            <?php foreach ($blog_posts as $post): ?>
              <div class="col-md-4">
                <div class="card shadow-sm h-100">
                  <img src="<?= (!empty($post['image']) && file_exists($post['image'])) ? htmlspecialchars($post['image']) : 'Images/default-blog-image.jpg' ?>"
                    alt="<?= htmlspecialchars($post['title']) ?>"
                    class="card-img-top" style="height:12rem; object-fit:cover;" />
                  <div class="card-body text-start">
                    <h3 class="card-title h5 text-dark"><?= htmlspecialchars($post['title']) ?></h3>
                    <p class="card-text text-secondary small mb-3"><?= htmlspecialchars(mb_strimwidth(strip_tags($post['content']), 0, 120, '...')) ?></p>
                    <a href="blog-post.php?id=<?= (int)$post['id'] ?>" class="text-success fw-semibold text-decoration-none">Read More</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-secondary">No blog posts available at the moment.</p>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <?php include("footer.php"); ?>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
