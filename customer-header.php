<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default to 'customer-home' if no ?page parameter
$currentPage = strtolower($_GET['page'] ?? 'customer-home');
$fullname = htmlspecialchars($_SESSION['fullname'] ?? 'Customer');
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
  /* Sidebar styling */
  #sidebar {
    width: 16rem;
    min-height: 100vh;
    background-color: #ffffff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
  }

  .nav-link {
    transition: background-color 0.2s ease, color 0.2s ease;
  }

  .nav-link:hover {
    background-color: #f0f0f0;
    color: #000;
  }

  .nav-link.active,
  .nav-link.bg-success {
    background-color: #198754 !important;
    color: #fff !important;
  }

  .nav-link.active i,
  .nav-link.bg-success i {
    color: #fff !important;
  }

  .nav-link i {
    font-size: 1rem;
    color: #6c757d;
  }

  .nav-link span {
    font-size: 0.95rem;
  }

  /* Mobile toggle button */
  @media (max-width: 991px) {
    #sidebar {
      position: fixed;
      top: 0;
      left: -16rem;
      height: 100%;
      z-index: 1030;
      transition: left 0.3s ease;
    }

    #sidebar.show {
      left: 0;
    }

    #sidebarBackdrop {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
      z-index: 1020;
    }

    #sidebarBackdrop.show {
      display: block;
    }
  }
</style>

<!-- Sidebar toggle button for mobile -->
<button class="btn btn-primary d-lg-none m-2" id="sidebarToggle">
  <i class="bi bi-list"></i> Menu
</button>

<!-- Sidebar backdrop for mobile -->
<div id="sidebarBackdrop"></div>

<!-- Sidebar -->
<aside id="sidebar" class="d-flex flex-column overflow-auto">
  <nav class="nav flex-column px-3 py-4">
    <?php 
    $navItems = [
      'customer-home'   => ['icon' => 'bi-house', 'label' => 'Home'],
      'profile-view'    => ['icon' => 'bi-person', 'label' => 'View/Edit Profile'],
      'register-class'  => ['icon' => 'bi-calendar-check', 'label' => 'Book Appointment'],
      'membership'      => ['icon' => 'bi-card-list', 'label' => 'Membership Plans'],
      'submit-query'    => ['icon' => 'bi-question-circle', 'label' => 'Submit a Query'],
      'blogs'           => ['icon' => 'bi-journal-text', 'label' => 'View Blogs']
    ];

    foreach ($navItems as $page => $data): 
      $isActive = ($currentPage === $page) ? 'active bg-success fw-semibold' : '';
    ?>
      <a href="?page=<?= $page ?>" class="nav-link d-flex align-items-center gap-2 py-2 px-2 rounded <?= $isActive ?>">
        <i class="<?= $data['icon'] ?>"></i>
        <span><?= $data['label'] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
</aside>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Mobile sidebar toggle script -->
<script>
  const sidebar = document.getElementById('sidebar');
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebarBackdrop = document.getElementById('sidebarBackdrop');

  sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('show');
    sidebarBackdrop.classList.toggle('show');
  });

  sidebarBackdrop.addEventListener('click', () => {
    sidebar.classList.remove('show');
    sidebarBackdrop.classList.remove('show');
  });
</script>
