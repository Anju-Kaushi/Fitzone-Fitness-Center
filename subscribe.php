<?php
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure only logged-in customers access this page
if (!isset($_SESSION['user']) || ($_SESSION['role'] ?? '') !== 'Customer') {
    header("Location: login.php?redirect=subscribe&plan_id=" . intval($_GET['plan_id'] ?? 0));
    exit();
}

$email = $_SESSION['user'];

// Use MySQLi prepared statements (your original code uses MySQLi)
$stmtUser = $conn->prepare("SELECT id FROM tbluser WHERE email = ?");
$stmtUser->bind_param("s", $email);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

if ($resultUser->num_rows === 0) {
    die("User not found.");
}

$user = $resultUser->fetch_assoc();
$user_id = $user['id'];

// Check if user has any approved membership (active membership check simplified)
$stmtActive = $conn->prepare("SELECT um.*, mp.name FROM user_memberships um 
                              JOIN membership_plans mp ON um.plan_id = mp.id
                              WHERE um.user_id = ? AND um.status = 'Approved' LIMIT 1");
$stmtActive->bind_param("i", $user_id);
$stmtActive->execute();
$resultActive = $stmtActive->get_result();

if ($resultActive->num_rows > 0) {
    $activeMembership = $resultActive->fetch_assoc();
    die("You already have an active membership (Plan: " . htmlspecialchars($activeMembership['name']) . "). You cannot subscribe to another plan until it expires or is cancelled.");
}

$planId = intval($_GET['plan_id'] ?? 0);

// Fetch selected plan details
$stmt = $conn->prepare("SELECT * FROM membership_plans WHERE id = ? AND status = 'Active'");
$stmt->bind_param("i", $planId);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();

if (!$plan) {
    die("Invalid or inactive plan selected.");
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Subscribe to Plan</title>
  <!-- Bootstrap 5 CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h1 class="card-title mb-4 text-dark"><?= htmlspecialchars($plan['name']) ?></h1>

            <ul class="list-unstyled mb-4">
              <li><strong>Monthly:</strong> LKR <?= number_format($plan['monthly_price'], 0) ?></li>
              <li><strong>Quarterly:</strong> LKR <?= number_format($plan['quarterly_price'], 0) ?></li>
              <li>
                <strong>Yearly:</strong> LKR <?= number_format($plan['yearly_price'], 0) ?>
                <?php if (!empty($plan['yearly_discount'])): ?>
                  <span class="text-success fw-semibold">(<?= htmlspecialchars($plan['yearly_discount']) ?> OFF)</span>
                <?php endif; ?>
              </li>
            </ul>

            <form method="POST" action="process_subscription.php" enctype="multipart/form-data">
              <input type="hidden" name="plan_id" value="<?= $planId ?>" />
              <label for="duration" class="form-label fw-medium mb-2">Select Duration:</label>
              <select id="duration" name="plan_type" required class="form-select mb-4">
                <option value="">-- Choose --</option>
                <option value="Monthly">Monthly</option>
                <option value="Quarterly">Quarterly</option>
                <option value="Yearly">Yearly</option>
              </select>

              <label for="payment_slip" class="form-label fw-medium mb-2">Upload Bank Deposit Slip:</label>
              <input type="file" name="payment_slip" id="payment_slip" accept=".jpg,.jpeg,.png,.pdf" required class="form-control mb-4" />

              <button type="submit" class="btn btn-success w-100">Confirm Subscription</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
