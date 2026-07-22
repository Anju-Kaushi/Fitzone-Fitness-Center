<?php
session_start();

// Access control for Admin only
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$fullname = htmlspecialchars($_SESSION['fullname'] ?? 'Admin');
$page = $_GET['page'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard | Fitzone Fitness Center</title>
</head>

<body class="bg-light" style="font-family: 'Roboto', sans-serif;">

  <?php include 'header.php'; ?>

  <div class="d-flex min-vh-100">
    
    <!-- Sidebar -->
    <?php include 'admin-header.php'; ?>

    <!-- Main content -->
    <main class="flex-grow-1 p-4 bg-white shadow-sm rounded m-3 overflow-auto">
      <?php
        switch ($page) {
          case 'dashboard':
            include 'dashboard-home.php';
            break;
          case 'users':
            include 'manage-users.php';
            break;
          case 'trainers':
            include 'manage-trainers.php';
            break;
          case 'schedule':
            include 'manage-schedule.php';
            break;
          case 'packages':
            include 'manage-packages.php';
            break;
          case 'membership':
            include 'manage-membership.php';
            break;
          case 'user-membership': 
            include 'membership_approval.php';
            break;
          case 'offers':
            include 'update-offers.php';
            break;
          case 'appointments':
            include 'manage-bookings.php';
            break;
          case 'queries':
            include 'manage-queries.php';
            break;
          case 'blog':
            include 'blog-manager.php';
            break;
          default:
            echo "<div class='alert alert-danger fw-bold'>⚠️ Page Not Found</div>";
            break;
        }
      ?>
    </main>
  </div>

  <?php include 'admin-footer.php'; ?>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
