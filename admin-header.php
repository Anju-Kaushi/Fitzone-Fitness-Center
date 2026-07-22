<?php
$page = $_GET['page'] ?? 'dashboard';
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
      'dashboard' => ['icon' => 'bi-house-door', 'label' => 'Home'],
      'users' => ['icon' => 'bi-people', 'label' => 'Manage Users'],
      'trainers' => ['icon' => 'bi-person-badge', 'label' => 'Manage Trainers'],
      'schedule' => ['icon' => 'bi-calendar2-week', 'label' => 'Manage Schedule'],
      'packages' => ['icon' => 'bi-gift', 'label' => 'Pricing & Packages'],
      'membership' => ['icon' => 'bi-credit-card', 'label' => 'Manage Membership Plans'],
      'user-membership' => ['icon' => 'bi-person-check', 'label' => 'Membership Approval'],
      'offers' => ['icon' => 'bi-tag', 'label' => 'Promotions & Offers'],
      'appointments' => ['icon' => 'bi-calendar-check', 'label' => 'Appointments'],
      'queries' => ['icon' => 'bi-chat-dots', 'label' => 'Customer Queries'],
      'blog' => ['icon' => 'bi-journal-text', 'label' => 'Blog Manager'],
    ];

    foreach ($navItems as $key => $item):
      $isActive = ($page === $key);
      $activeClass = $isActive ? 'active bg-success fw-semibold' : '';
      $url = htmlspecialchars("admin-dashboard.php?page=$key");
      $icon = htmlspecialchars($item['icon']);
      $label = htmlspecialchars($item['label']);
    ?>
      <a href="<?= $url ?>" class="nav-link d-flex align-items-center rounded mb-1 px-3 py-2 <?= $activeClass ?>">
        <i class="<?= $icon ?> me-2"></i>
        <span><?= $label ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
</aside>

<!-- Bootstrap JS Bundle -->
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
