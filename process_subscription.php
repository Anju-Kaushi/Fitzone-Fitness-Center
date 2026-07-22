<?php
include 'db.php';
session_start();

// Check login and role
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Customer') {
    header("Location: login.php");
    exit();
}

$email = trim($_SESSION['user']);

// Get user ID from email
$stmtUser = $conn->prepare("SELECT id FROM tbluser WHERE email = ?");
if (!$stmtUser) {
    die("Error preparing user query: " . $conn->error);
}
$stmtUser->bind_param("s", $email);
$stmtUser->execute();
$stmtUser->bind_result($userId);
$stmtUser->fetch();
$stmtUser->close();

if (empty($userId)) {
    die("User not found.");
}

// Get form values
$planId = intval($_POST['plan_id'] ?? 0);
$planTypeRaw = strtolower(trim($_POST['plan_type'] ?? ''));
$planType = ucfirst($planTypeRaw);
$paymentSlip = $_FILES['payment_slip'] ?? null;

$validDurations = ['Monthly', 'Quarterly', 'Yearly'];

if ($planId <= 0) {
    die("❌ Invalid plan selected.");
}

if (!in_array($planType, $validDurations)) {
    die("❌ Invalid plan duration selected.");
}

if (!$paymentSlip || $paymentSlip['error'] !== UPLOAD_ERR_OK) {
    die("❌ Payment slip upload failed. Please try again.");
}

// Save uploaded file
$targetDir = __DIR__ . "/uploads/payment_slips/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$originalName = basename($paymentSlip['name']);
$extension = pathinfo($originalName, PATHINFO_EXTENSION);
$filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
$targetFile = $targetDir . $filename;

// Move uploaded file
if (!move_uploaded_file($paymentSlip['tmp_name'], $targetFile)) {
    die("❌ Failed to save uploaded payment slip.");
}

// Insert into user_memberships
$stmt = $conn->prepare("INSERT INTO user_memberships (user_id, plan_id, plan_type, payment_slip) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    die("Error preparing insert query: " . $conn->error);
}

$stmt->bind_param("iiss", $userId, $planId, $planType, $filename);

if ($stmt->execute()) {
    echo "<p class='text-success fw-bold'>✅ Subscription submitted successfully! Your payment will be reviewed shortly.</p>";
    echo "<p><a href='customer-dashboard.php'>Go to Dashboard</a></p>";
} else {
    echo "<p class='text-danger'>❌ Error subscribing: " . htmlspecialchars($stmt->error) . "</p>";
}

$stmt->close();
$conn->close();
?>
