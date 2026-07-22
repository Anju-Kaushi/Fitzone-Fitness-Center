<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check login
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Customer') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'];

// Fetch user info and membership features
$stmtUser = $conn->prepare("
    SELECT u.id AS user_id, mp.features
    FROM tbluser u
    JOIN user_memberships um ON u.id = um.user_id
    JOIN membership_plans mp ON um.plan_id = mp.id
    WHERE u.email = ? AND um.status = 'Approved'
    ORDER BY um.created_at DESC LIMIT 1
");
$stmtUser->bind_param("s", $email);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

if ($resultUser->num_rows === 0) {
    die("❌ You must have an approved membership to book classes.");
}

$userData = $resultUser->fetch_assoc();
$userId = (int)$userData['user_id'];
$featuresText = $userData['features'];

if (stripos($featuresText, 'group class') === false) {
    die("❌ Your membership does not include group class access.");
}

// Fetch classes
$classQuery = "
    SELECT cs.id, cs.class_name, cs.day, cs.time, COALESCE(t.name, 'TBD') AS trainer_name
    FROM class_schedule cs
    LEFT JOIN trainers t ON cs.trainer_id = t.id
    ORDER BY FIELD(cs.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), cs.time
";
$classResult = $conn->query($classQuery);
if (!$classResult) {
    die("Class query failed: " . $conn->error);
}

// Fetch booked class IDs
$bookedClassIds = [];
$stmtBooked = $conn->prepare("SELECT class_id FROM appointments WHERE user_id = ? AND status IN ('pending', 'confirmed')");
$stmtBooked->bind_param("i", $userId);
$stmtBooked->execute();
$resBooked = $stmtBooked->get_result();
while ($row = $resBooked->fetch_assoc()) {
    $bookedClassIds[] = (int)$row['class_id'];
}

// Fetch user's appointments
$stmtBookings = $conn->prepare("
    SELECT a.id, a.appointment_datetime, a.status, cs.class_name, cs.day, cs.time, COALESCE(t.name, 'TBD') AS trainer_name
    FROM appointments a
    JOIN class_schedule cs ON a.class_id = cs.id
    LEFT JOIN trainers t ON cs.trainer_id = t.id
    WHERE a.user_id = ?
    ORDER BY a.appointment_datetime DESC
");
$stmtBookings->bind_param("i", $userId);
$stmtBookings->execute();
$bookingsResult = $stmtBookings->get_result();

$pendingBookings = [];
$confirmedBookings = [];
while ($booking = $bookingsResult->fetch_assoc()) {
    $status = strtolower($booking['status']);
    if ($status === 'pending') {
        $pendingBookings[] = $booking;
    } elseif ($status === 'confirmed') {
        $confirmedBookings[] = $booking;
    }
}

// Get next date of a specific day
function getNextDateOfDay($dayName, $time) {
    $daysOfWeek = [
        'Monday'    => 1,
        'Tuesday'   => 2,
        'Wednesday' => 3,
        'Thursday'  => 4,
        'Friday'    => 5,
        'Saturday'  => 6,
        'Sunday'    => 7
    ];

    $targetDayNum = $daysOfWeek[ucfirst(strtolower($dayName))];
    $now = new DateTime();
    $todayDayNum = (int)$now->format('N');

    // Calculate days until the target day
    $daysUntil = ($targetDayNum - $todayDayNum + 7) % 7;

    // Build DateTime for the next class occurrence
    $classDateTime = (clone $now)->modify("+$daysUntil days")->setTime(
        (int)substr($time, 0, 2),
        (int)substr($time, 3, 2)
    );

    // If it's today and the time already passed, move to next week
    if ($daysUntil === 0 && $classDateTime <= $now) {
        $classDateTime->modify("+7 days");
    }

    return $classDateTime->format('Y-m-d H:i:s');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Book a Group Class</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: #f8f9fa;
    }
    .badge-status {
      font-size: 0.9em;
    }
  </style>
</head>
<body>
<div class="container py-5">
  <div class="text-center mb-5">
    <h2 class="fw-bold text-primary">Book a Group Class</h2>
    <p class="text-muted">Choose an available class to join</p>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <form action="submit-booking.php" method="POST" id="bookingForm">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId) ?>" />
            <div class="mb-3">
              <label for="class_id" class="form-label fw-semibold">Select a Class:</label>
              <select name="class_id" id="class_id" required class="form-select">
                <option value="">-- Choose a class --</option>
                <?php
                $classResult->data_seek(0);
                $anyAvailable = false;
                while ($row = $classResult->fetch_assoc()) {
                    if (in_array((int)$row['id'], $bookedClassIds)) continue;
                    $anyAvailable = true;
                    $nextDateTime = getNextDateOfDay($row['day'], $row['time']);
                    ?>
                    <option value="<?= htmlspecialchars($row['id']) ?>">
                        <?= htmlspecialchars($row['class_name']) ?> with <?= htmlspecialchars($row['trainer_name']) ?>
                        — <?= htmlspecialchars($row['day']) ?> at <?= htmlspecialchars($row['time']) ?>
                        (Next: <?= date('D, M d Y H:i', strtotime($nextDateTime)) ?>)
                    </option>
                <?php } ?>
                <?php if (!$anyAvailable): ?>
                    <option disabled>No available classes to book</option>
                <?php endif; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Confirm Booking</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Pending Bookings -->
  <div class="mb-4">
    <h4 class="text-warning">⏳ Your Pending Bookings</h4>
    <?php if (count($pendingBookings)): ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Class</th>
              <th>Trainer</th>
              <th>Day</th>
              <th>Time</th>
              <th>Appointment</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pendingBookings as $b): ?>
              <tr>
                <td><?= htmlspecialchars($b['class_name']) ?></td>
                <td><?= htmlspecialchars($b['trainer_name']) ?></td>
                <td><?= htmlspecialchars($b['day']) ?></td>
                <td><?= htmlspecialchars($b['time']) ?></td>
                <td><?= htmlspecialchars($b['appointment_datetime']) ?></td>
                <td><span class="badge bg-warning text-dark badge-status"><?= htmlspecialchars($b['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted">You have no pending bookings.</p>
    <?php endif; ?>
  </div>

  <!-- Confirmed Bookings -->
  <div>
    <h4 class="text-success">✅ Your Confirmed Bookings</h4>
    <?php if (count($confirmedBookings)): ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Class</th>
              <th>Trainer</th>
              <th>Day</th>
              <th>Time</th>
              <th>Appointment</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($confirmedBookings as $b): ?>
              <tr>
                <td><?= htmlspecialchars($b['class_name']) ?></td>
                <td><?= htmlspecialchars($b['trainer_name']) ?></td>
                <td><?= htmlspecialchars($b['day']) ?></td>
                <td><?= htmlspecialchars($b['time']) ?></td>
                <td><?= htmlspecialchars($b['appointment_datetime']) ?></td>
                <td><span class="badge bg-success badge-status"><?= htmlspecialchars($b['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted">No confirmed bookings yet.</p>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById("bookingForm").addEventListener("submit", function(e) {
    if (!confirm("Are you sure you want to book this class?")) {
        e.preventDefault();
    }
});
</script>
</body>
</html>
