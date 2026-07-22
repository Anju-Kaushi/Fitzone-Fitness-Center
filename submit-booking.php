<?php
include 'db.php';
session_start();

// Ensure only logged-in customers can book
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Customer') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user']; // Email stored in session

// Retrieve user_id from email
$stmtUser = $conn->prepare("SELECT id FROM tbluser WHERE email = ?");
if (!$stmtUser) {
    die("Database error: " . $conn->error);
}
$stmtUser->bind_param("s", $email);
$stmtUser->execute();
$stmtUser->bind_result($user_id);
$stmtUser->fetch();
$stmtUser->close();

if (empty($user_id)) {
    die("User not found.");
}

// Validate class_id from form
$class_id = $_POST['class_id'] ?? null;
if (!$class_id || !is_numeric($class_id)) {
    die("Invalid class selection.");
}

// Fetch selected class details
$stmt = $conn->prepare("SELECT day, time FROM class_schedule WHERE id = ?");
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Class not found.");
}

$class = $result->fetch_assoc();
$day = $class['day'];
$time = $class['time'];

/**
 * Get the next occurrence of the class day & time.
 * Ensures that if today's class time has passed, booking is for next week.
 */
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

    $targetDayNum = $daysOfWeek[ucfirst(strtolower($dayName))] ?? null;
    if (!$targetDayNum) {
        return null; // Invalid day name
    }

    $today = new DateTime();
    $todayDayNum = (int)$today->format('N');
    $daysUntil = ($targetDayNum - $todayDayNum + 7) % 7;

    // If it's the same day but time has passed, schedule for next week
    if ($daysUntil === 0 && $today->format('H:i') >= $time) {
        $daysUntil = 7;
    }

    return (clone $today)->modify("+$daysUntil days")->format('Y-m-d') . " $time:00";
}

$appointment_datetime = getNextDateOfDay($day, $time);

if (!$appointment_datetime) {
    die("Invalid class schedule day.");
}

// Prevent duplicate booking for same class session
$check = $conn->prepare("
    SELECT 1 
    FROM appointments 
    WHERE user_id = ? 
      AND class_id = ? 
      AND appointment_datetime = ?
");
if (!$check) {
    die("Database error: " . $conn->error);
}
$check->bind_param("iis", $user_id, $class_id, $appointment_datetime);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    die("You have already booked this class for the upcoming session.");
}

// Insert booking
$insert = $conn->prepare("
    INSERT INTO appointments (user_id, class_id, appointment_datetime, status) 
    VALUES (?, ?, ?, 'Pending')
");
if (!$insert) {
    die("Database error: " . $conn->error);
}
$insert->bind_param("iis", $user_id, $class_id, $appointment_datetime);

if ($insert->execute()) {
    header("Location: customer-dashboard.php?booking=success");
    exit();
} else {
    die("Booking failed: " . $insert->error);
}

$conn->close();
?>
