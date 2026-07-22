<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

// Redirect if not logged in or not admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Get stats
$userCount = $appointmentCount = $unreadQueryCount = 0;

$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tbluser");
if ($row = mysqli_fetch_assoc($res)) $userCount = $row['total'];

$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM appointments WHERE DATE(appointment_datetime) = CURDATE()");
if ($row = mysqli_fetch_assoc($res)) $appointmentCount = $row['total'];

$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM queries WHERE status = 'unread'");
if ($row = mysqli_fetch_assoc($res)) $unreadQueryCount = $row['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Home - Fitzone</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h2 class="mb-4 text-center text-primary">Admin Dashboard</h2>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card text-bg-primary h-100">
        <div class="card-body text-center">
          <i class="fas fa-users fa-2x mb-2"></i>
          <h5 class="card-title">Total Users</h5>
          <p class="display-6"><?= $userCount ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-bg-success h-100">
        <div class="card-body text-center">
          <i class="fas fa-calendar-check fa-2x mb-2"></i>
          <h5 class="card-title">Today’s Appointments</h5>
          <p class="display-6"><?= $appointmentCount ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-bg-warning h-100">
        <div class="card-body text-center">
          <i class="fas fa-envelope fa-2x mb-2"></i>
          <h5 class="card-title">Unread Queries</h5>
          <p class="display-6"><?= $unreadQueryCount ?></p>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-5">
    <h4>Recent Appointments</h4>
    <div class="table-responsive mt-3">
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th>User Name</th>
            <th>Phone</th>
            <th>Class</th>
            <th>Date & Time</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $query = "
            SELECT u.fullname, u.phone, cs.class_name, a.appointment_datetime, a.status
            FROM appointments a
            JOIN tbluser u ON a.user_id = u.id
            JOIN class_schedule cs ON a.class_id = cs.id
            ORDER BY a.appointment_datetime DESC
            LIMIT 5
          ";
          $res = mysqli_query($conn, $query);
          if (mysqli_num_rows($res) > 0) {
              while ($row = mysqli_fetch_assoc($res)) {
                  echo "<tr>
                          <td>" . htmlspecialchars($row['fullname']) . "</td>
                          <td>" . htmlspecialchars($row['phone']) . "</td>
                          <td>" . htmlspecialchars($row['class_name']) . "</td>
                          <td>" . htmlspecialchars(date("Y-m-d H:i", strtotime($row['appointment_datetime']))) . "</td>
                          <td>" . htmlspecialchars($row['status']) . "</td>
                        </tr>";
              }
          } else {
              echo "<tr><td colspan='5' class='text-center'>No appointments found.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
