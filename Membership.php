<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Get today's date for offers
$today = date('Y-m-d');
$offersStmt = $conn->prepare("SELECT * FROM offers WHERE valid_until >= ? ORDER BY valid_until ASC");
$offersStmt->bind_param('s', $today);
$offersStmt->execute();
$offers = $offersStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get active membership plans
$plansStmt = $conn->prepare("SELECT * FROM membership_plans WHERE status = 'active' ORDER BY id ASC");
$plansStmt->execute();
$plans = $plansStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Membership | FitZone Fitness Center</title>

  <!-- Bootstrap & Google Fonts -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f9fafb;
    }

    img[onerror] {
      object-fit: cover;
    }

    .plan-blue { border-color: #0d6efd !important; color: #0d6efd !important; }
    .plan-purple { border-color: #6f42c1 !important; color: #6f42c1 !important; }
    .plan-yellow { border-color: #ffc107 !important; color: #ffc107 !important; }
    .plan-gray { border-color: #6c757d !important; color: #6c757d !important; }

    .btn-plan-blue { background-color: #0d6efd; color: white; }
    .btn-plan-purple { background-color: #6f42c1; color: white; }
    .btn-plan-yellow { background-color: #ffc107; color: black; }
    .btn-plan-gray { background-color: #6c757d; color: white; }

    .btn-plan-blue:hover { background-color: #0b5ed7; }
    .btn-plan-purple:hover { background-color: #5a32a3; }
    .btn-plan-yellow:hover { background-color: #e0a800; }
    .btn-plan-gray:hover { background-color: #5a6268; }

    .material-icons { vertical-align: middle; font-size: 18px; }

    /* Carousel image */
    .offer-image {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 0.375rem;
    }

    /* Description box with max height & scroll */
    .offer-description {
      max-height: 140px;
      overflow-y: auto;
      white-space: pre-line;
      color: #6c757d;
    }
  </style>
</head>

<body class="bg-white text-gray-800">
<?php include("header.php"); ?>

<main class="min-vh-100">
  <!-- Hero Section -->
  <div class="position-relative shadow-lg" style="height: 24rem;">
    <img src="Images/People get membership.png" alt="People joining membership"
         class="w-100 h-100 object-fit-cover rounded-bottom" />
    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center text-center p-4"
         style="background: linear-gradient(to bottom, rgba(0,0,0,0.6), rgba(0,0,0,0.3)); color: white;">
      <h1 class="display-4 fw-bold text-shadow">Choose Your Membership Plan</h1>
      <p class="lead fw-medium max-w-75 mx-auto mt-3">Flexible plans tailored to your fitness journey</p>
    </div>
  </div>

  <!-- Offers Section -->
  <section class="bg-white py-5 max-w-90 mx-auto rounded shadow mt-5 container">
    <h2 class="text-center mb-4 text-dark fw-bold fs-3">Current Offers & Promotions</h2>
    <?php if (count($offers) > 0): ?>
      <div id="offersCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="7000" aria-label="Current Offers Carousel">
        <div class="carousel-indicators">
          <?php foreach ($offers as $index => $offer): ?>
            <button type="button" data-bs-target="#offersCarousel" data-bs-slide-to="<?= $index ?>"
                    class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>"
                    aria-label="Slide <?= $index + 1 ?>"></button>
          <?php endforeach; ?>
        </div>

        <div class="carousel-inner">
          <?php foreach ($offers as $index => $offer): ?>
            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
              <div class="row align-items-center">
                <div class="col-md-5">
                  <?php if (!empty($offer['image']) && file_exists($offer['image'])): ?>
                    <img src="<?= htmlspecialchars($offer['image']) ?>" alt="<?= htmlspecialchars($offer['title']) ?>"
                         onerror="this.onerror=null;this.src='Images/placeholder.jpg';"
                         class="offer-image" />
                  <?php else: ?>
                    <div class="bg-secondary bg-opacity-25 text-secondary fst-italic d-flex justify-content-center align-items-center rounded"
                         style="height: 200px; font-size: 1.25rem;">
                      No Image Available
                    </div>
                  <?php endif; ?>
                </div>

                <div class="col-md-7">
                  <h3 class="fw-semibold text-dark"><?= htmlspecialchars($offer['title']) ?></h3>
                  <p class="offer-description">
                    <?= nl2br(htmlspecialchars($offer['description'])) ?>
                  </p>
                  <p class="text-success fw-semibold small mt-3">
                    Valid Until: <time datetime="<?= htmlspecialchars($offer['valid_until']) ?>">
                      <?= date('F j, Y', strtotime($offer['valid_until'])) ?>
                    </time>
                  </p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#offersCarousel" data-bs-slide="prev" aria-label="Previous Offer">
          <span class="material-icons" aria-hidden="true" style="font-size: 36px; color: #0d6efd;">arrow_back_ios</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#offersCarousel" data-bs-slide="next" aria-label="Next Offer">
          <span class="material-icons" aria-hidden="true" style="font-size: 36px; color: #0d6efd;">arrow_forward_ios</span>
        </button>
      </div>
    <?php else: ?>
      <p class="text-center text-muted fst-italic fs-5">No current offers available.</p>
    <?php endif; ?>
  </section>

  <!-- Membership Plans -->
  <section class="py-5 max-w-90 mx-auto container">
    <h2 class="text-center mb-5 text-dark fw-bold fs-2">Our Membership Tiers</h2>
    <div class="row g-4">
      <?php foreach ($plans as $plan):
        $features = explode(';', $plan['features']);
        $color = match (strtolower($plan['name'])) {
          'basic plan' => 'blue',
          'standard plan' => 'purple',
          'premium plan' => 'yellow',
          default => 'gray'
        };

        $isCustomer = isset($_SESSION['user']) && ($_SESSION['role'] ?? '') === 'Customer';
        $planLink = $isCustomer
            ? "subscribe.php?plan_id={$plan['id']}"
            : "login.php?redirect=subscribe&plan_id={$plan['id']}";
      ?>
        <div class="col-md-4">
          <div class="card h-100 border-4 plan-<?= $color ?> shadow-sm hover-scale">
            <div class="card-body d-flex flex-column">
              <h3 class="card-title fw-bold text-center text-<?= $color ?> mb-4 border-bottom pb-2">
                <?= htmlspecialchars($plan['name']) ?>
              </h3>

              <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item d-flex justify-content-between align-items-center fw-semibold text-<?= $color ?>">
                  Monthly Price <span>LKR <?= number_format($plan['monthly_price'], 0) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center fw-semibold text-<?= $color ?>">
                  Quarterly Price <span>LKR <?= number_format($plan['quarterly_price'], 0) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center fw-semibold text-<?= $color ?>">
                  Yearly Price <span>
                    LKR <?= number_format($plan['yearly_price'], 0) ?>
                    <?php if (!empty($plan['yearly_discount'])): ?>
                      <span class="text-success">(<?= htmlspecialchars($plan['yearly_discount']) ?>)</span>
                    <?php endif; ?>
                  </span>
                </li>
              </ul>

              <ul class="flex-grow-1 list-unstyled text-<?= $color ?> small mb-4">
                <?php foreach ($features as $feature): ?>
                  <li class="d-flex align-items-center mb-2">
                    <span class="material-icons text-<?= $color ?> me-2">check_circle</span>
                    <?= htmlspecialchars(trim($feature)) ?>
                  </li>
                <?php endforeach; ?>
              </ul>

              <a href="<?= $planLink ?>" class="btn btn-plan-<?= $color ?> mt-auto fw-semibold shadow-sm w-100">
                Join Now
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

</main>

<?php include 'footer.php'; ?>

<!-- Bootstrap JS + Hover Scale -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.querySelectorAll('.hover-scale').forEach(card => {
    card.style.transition = 'transform 0.3s ease';
    card.addEventListener('mouseenter', () => card.style.transform = 'scale(1.03)');
    card.addEventListener('mouseleave', () => card.style.transform = 'scale(1)');
  });
</script>
</body>
</html>
