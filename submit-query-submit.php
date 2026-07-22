<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

// Ensure user is logged in and is a Customer
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Customer') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_SESSION['fullname'] ?? '');
    $email = trim($_SESSION['user'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $inquiry_type = trim($_POST['inquiry_type'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate required fields
    if (!$full_name || !$email || !$inquiry_type || !$message) {
        $_SESSION['query_error'] = "Please fill all required fields.";
        header("Location: submit-query.php");
        exit();
    }

    $status = 'Unread'; // IMPORTANT: set default status here

    // Insert query
    $stmt = $conn->prepare("INSERT INTO queries (full_name, email, phone, inquiry_type, message, status, submitted_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssss", $full_name, $email, $phone, $inquiry_type, $message, $status);

    if ($stmt->execute()) {
        $_SESSION['query_success'] = "Your query has been submitted successfully.";
    } else {
        $_SESSION['query_error'] = "Failed to submit your query. Please try again.";
    }

    $stmt->close();
    $conn->close();

    header("Location: submit-query.php");
    exit();
} else {
    header("Location: submit-query.php");
    exit();
}
