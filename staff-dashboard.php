<?php
session_start();
include 'db.php';

// Access control for Staff only
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$fullname = htmlspecialchars($_SESSION['fullname'] ?? 'Staff');
$page = $_GET['page'] ?? 'staff-home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Staff Dashboard | Fitzone Fitness Center</title>
</head>

<body class="bg-light" style="font-family: 'Roboto', sans-serif;">

<?php include 'header.php'; ?>

<div class="d-flex min-vh-100">

    <!-- Sidebar -->
      <?php include 'staff-header.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow-1 p-4 bg-white shadow-sm rounded m-3 overflow-auto">
      <?php
      switch ($page) {
        case 'staff-home':
          include 'staff-home.php';
          break;

        case 'appointments':
          include 'manage-bookings.php';
          break;

        case 'schedule':
          include 'manage-schedule.php';
          break;

        case 'trainers':
          include 'manage-trainers.php';
          break;

        case 'queries':
          include 'manage-queries.php';
          break;

        case 'blog':
          include 'blog-manager.php';
          break;

        case 'offers':
          include 'update-offers.php';
          break;

        default:
          echo "<div class='alert alert-danger text-center fw-bold'>⚠️ Page not found.</div>";
          break;
      }
      ?>
    </main>
</div>

<?php include 'staff-footer.php'; ?>

</body>
</html>
