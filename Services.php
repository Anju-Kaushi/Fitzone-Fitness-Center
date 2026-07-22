<?php
session_start();
include("header.php");
include("db.php"); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Services | FitZone Fitness Center</title>

  <!-- CSS Libraries -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <style>
    body {
      font-family: 'Roboto', sans-serif;
    }
    .icon-badge {
      position: absolute;
      top: 15px;
      left: 15px;
      background-color: #198754;
      color: #fff;
      border-radius: 50%;
      padding: 10px;
      font-size: 24px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    .card-img-top {
      height: 220px;
      object-fit: cover;
    }
  </style>
</head>
<body class="bg-light text-dark">

<!-- Hero Section -->
<div class="bg-white py-5">
  <div class="container d-flex flex-column flex-md-row align-items-center">
    <div class="text-center text-md-start mb-4 mb-md-0 col-md-6 pe-md-5">
      <h1 class="display-4 fw-bold text-success">Our Services</h1>
      <h2 class="h4 text-secondary mb-3">Explore Our Fitness Services</h2>
      <p class="text-muted mb-4">
        Empowering your health, one workout at a time. Discover personalized fitness solutions crafted for your journey.
      </p>
      <button 
        class="btn btn-success px-4 py-2"
        onclick="<?php echo (!empty($_SESSION['user'])) 
          ? "alert('You are already logged in!');" 
          : "window.location.href='login.php';"; ?>">
        Join Now
      </button>
    </div>
    <div class="col-md-6">
      <img src="Images/Women & Men Workout.jpg" alt="Workout" class="img-fluid rounded shadow">
    </div>
  </div>
</div>

<!-- Services Section -->
<div class="py-5 bg-light">
  <div class="container">
    <h2 class="text-center fw-bold text-primary mb-5">What We Offer</h2>
    <div class="row g-4">

      <?php
      $query = "SELECT * FROM services ORDER BY id ASC";
      $result = $conn->query($query);

      if ($result && $result->num_rows > 0):
        while ($service = $result->fetch_assoc()):
          $serviceTitle = htmlspecialchars($service['title']);
          $serviceDesc  = htmlspecialchars($service['description']);
          $serviceImg   = htmlspecialchars($service['image']);
          $serviceIcon  = htmlspecialchars($service['icon']);
      ?>
        <div class="col-sm-6 col-lg-4">
          <div class="card shadow-sm h-100 position-relative">
            <img src="<?php echo $serviceImg; ?>" alt="<?php echo $serviceTitle; ?>" class="card-img-top">
            <?php if (!empty($serviceIcon)): ?>
              <span class="material-icons icon-badge"><?php echo $serviceIcon; ?></span>
            <?php endif; ?>
            <div class="card-body">
              <h5 class="card-title"><?php echo $serviceTitle; ?></h5>
              <p class="card-text text-muted"><?php echo $serviceDesc; ?></p>
              <button 
                class="btn btn-success px-4 py-2"
                onclick="<?php echo (!empty($_SESSION['user'])) 
                  ? "alert('You are already logged in!');" 
                  : "window.location.href='login.php';"; ?>">
                Join Now
              </button>
            </div>
          </div>
        </div>
      <?php
        endwhile;
      else:
        echo '<p class="text-center text-muted">No services available right now.</p>';
      endif;
      ?>

    </div>
  </div>
</div>

<?php include("footer.php"); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
