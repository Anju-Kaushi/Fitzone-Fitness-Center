<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

// Restrict access to staff only
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Staff') {
    header("Location: login.php");
    exit();
}

$fullname = htmlspecialchars($_SESSION['fullname'] ?? 'Staff');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staff Home - Fitzone</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> 
</head>

<body>
<div class="container mt-4">
    <h1 class="mb-4 text-center text-primary">Welcome, <?= $fullname ?> 👋</h1>

    <!-- Bookings Section -->
    <section class="mb-5">
        <h2 class="h4 text-dark mb-3">Existing Class Bookings</h2>
        <div class="table-responsive shadow-sm bg-white rounded">
            <table class="table table-hover table-bordered mb-0 text-nowrap">
                <thead class="table-success">
                    <tr>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Class</th>
                        <th>Trainer</th>
                        <th>Date &amp; Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("
                        SELECT a.*, u.fullname AS customer_name, u.email, cs.class_name, cs.day, cs.time, t.name AS trainer_name
                        FROM appointments a
                        JOIN tbluser u ON a.user_id = u.id
                        JOIN class_schedule cs ON a.class_id = cs.id
                        JOIN trainers t ON cs.trainer_id = t.id
                        ORDER BY a.appointment_datetime DESC
                    ");
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['class_name']) ?></td>
                        <td><?= htmlspecialchars($row['trainer_name']) ?></td>
                        <td><?= htmlspecialchars(date("Y-m-d H:i", strtotime($row['appointment_datetime']))) ?></td>
                        <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                    </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No bookings available.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Queries Section -->
    <section>
        <h2 class="h4 text-dark mb-3">Customer Queries</h2>
        <div class="table-responsive shadow-sm bg-white rounded">
            <table class="table table-hover table-bordered mb-0 text-nowrap">
                <thead class="table-success">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $queryResult = mysqli_query($conn, "SELECT * FROM queries ORDER BY submitted_at DESC");

                    if (mysqli_num_rows($queryResult) > 0):
                        while ($query = mysqli_fetch_assoc($queryResult)):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($query['full_name']) ?></td>
                        <td><?= htmlspecialchars($query['email']) ?></td>
                        <td><?= htmlspecialchars($query['phone']) ?></td>
                        <td><?= htmlspecialchars($query['inquiry_type']) ?></td>
                        <td><?= htmlspecialchars($query['message']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($query['status'])) ?></td>
                        <td><?= htmlspecialchars($query['submitted_at']) ?></td>
                    </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No queries submitted yet.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
