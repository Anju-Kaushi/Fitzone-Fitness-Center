<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page filename
$currentPage = basename($_SERVER['PHP_SELF']);

// Utility function to check active nav item
function isActive($page, $current)
{
    return $current === $page ? 'nav-link nav-active' : 'nav-link';
}

// Display name logic
$displayName = '';
if (isset($_SESSION['fullname'])) {
    $displayName = htmlspecialchars($_SESSION['fullname']);
} elseif (isset($_SESSION['user'])) {
    $displayName = htmlspecialchars($_SESSION['user']);
}

// Determine dashboard link based on role
$dashboardLink = 'index.php';
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'Admin':
            $dashboardLink = 'admin-dashboard.php';
            break;
        case 'Staff':
            $dashboardLink = 'staff-dashboard.php';
            break;
        case 'Customer':
            $dashboardLink = 'customer-dashboard.php';
            break;
    }
}
?>

<!-- Bootstrap CSS & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Optional Custom Style -->
<style>
  .nav-active {
    color: white !important;
    background-color: #0d6efd !important;
    border-radius: 0.25rem;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
  }
  @media (max-width: 767px) {
    .navbar-brand img {
      height: 40px; /* smaller logo for mobile */
    }
    .navbar-nav .nav-link {
      font-size: 1rem;
      padding: 0.5rem 1rem;
    }
  }
</style>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow sticky-top py-2">
  <div class="container-fluid">
    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="Images/Logo.png" alt="Fitzone logo" style="height: 50px;">
    </a>

    <!-- Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collapsible Nav -->
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <!-- Centered Nav Links -->
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="<?= isActive('index.php', $currentPage) ?>" href="index.php">Home</a></li>
        <li class="nav-item"><a class="<?= isActive('Aboutus.php', $currentPage) ?>" href="Aboutus.php">About Us</a></li>
        <li class="nav-item"><a class="<?= isActive('Services.php', $currentPage) ?>" href="Services.php">Services</a></li>
        <li class="nav-item"><a class="<?= isActive('Schedule.php', $currentPage) ?>" href="Schedule.php">Schedules</a></li>
        <li class="nav-item"><a class="<?= isActive('trainers.php', $currentPage) ?>" href="trainers.php">Trainers</a></li>
        <li class="nav-item"><a class="<?= isActive('Membership.php', $currentPage) ?>" href="Membership.php">Membership</a></li>
        <li class="nav-item"><a class="<?= isActive('Blog.php', $currentPage) ?>" href="Blog.php">Blog</a></li>
        <li class="nav-item"><a class="<?= isActive('Contact.php', $currentPage) ?>" href="Contact.php">Contact Us</a></li>
      </ul>

      <!-- Right Side User Controls -->
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <?php if (isset($_SESSION['user'])): ?>
          <li class="nav-item d-flex align-items-center">
            <a class="nav-link text-success d-flex align-items-center gap-1 fw-semibold" href="<?= htmlspecialchars($dashboardLink) ?>" title="Dashboard">
              <i class="bi bi-grid-fill fs-5"></i> 
            </a>
          </li>

          <li class="nav-item d-flex align-items-center">
            <span class="navbar-text text-dark small">Welcome, <?= $displayName ?></span>
          </li>

          <li class="nav-item d-flex align-items-center">
            <a class="nav-link text-danger fw-semibold" href="logout.php" onclick="return confirmLogout();">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <!-- Login link with no button color -->
            <a class="nav-link" href="Login.php">Login</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- JavaScript for Logout Confirmation -->
<script>
  function confirmLogout() {
    return confirm("Are you sure you want to logout?");
  }
</script>
