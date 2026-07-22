<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $full_name     = trim($_POST['name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $inquiry_type  = trim($_POST['inquiry'] ?? '');
    $message       = trim($_POST['message'] ?? '');

    $status        = 'Unread'; // Default status for new queries
    $submitted_at  = date('Y-m-d H:i:s');

    // Validate required fields
    if (!empty($full_name) && !empty($email) && !empty($inquiry_type) && !empty($message)) {
        
        // Optional: validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: contact.php?error=Invalid+email+format");
            exit();
        }

        // Optional: validate phone format if provided
        if (!empty($phone) && !preg_match('/^\+?\d{7,15}$/', $phone)) {
            header("Location: contact.php?error=Invalid+phone+number+format");
            exit();
        }

        // Insert into database using prepared statement
        $stmt = $conn->prepare("
            INSERT INTO queries (full_name, email, phone, inquiry_type, message, status, submitted_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("sssssss", $full_name, $email, $phone, $inquiry_type, $message, $status, $submitted_at);

        if ($stmt->execute()) {
            header("Location: contact.php?success=1");
            exit();
        } else {
            header("Location: contact.php?error=Unable+to+save+your+message");
            exit();
        }
        $stmt->close();
    } else {
        header("Location: contact.php?error=Please+fill+in+all+required+fields");
        exit();
    }
} else {
    header("Location: contact.php");
    exit();
}

$conn->close();
?>
