<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim inputs
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    $role = strtolower(trim($_POST['role'])); // Ensure lowercase
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Only allow Customer registration
    if ($role !== 'customer') {
        header("Location: Registration.php?error=Only+Customer+registration+is+allowed");
        exit();
    }

    // 2. Password confirmation
    if ($password !== $confirm_password) {
        header("Location: Registration.php?error=Passwords+do+not+match");
        exit();
    }

    // 3. Check for duplicate email
    $stmt = $conn->prepare("SELECT id FROM tbluser WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        header("Location: Registration.php?error=Email+is+already+registered");
        exit();
    }
    $stmt->close();

    // 4. Optional phone validation
    if ($phone && !preg_match('/^\+?\d{7,15}$/', $phone)) {
        header("Location: Registration.php?error=Invalid+phone+number+format");
        exit();
    }

    // 5. Hash password securely
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 6. Insert new user
    $insert = $conn->prepare("INSERT INTO tbluser (fullname, email, phone, role, password) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("sssss", $fullname, $email, $phone, ucfirst($role), $hashed_password);

    if ($insert->execute()) {
        header("Location: Registration.php?success=Registration+successful!+Please+login.");
    } else {
        header("Location: Registration.php?error=Registration+failed.+Try+again.");
    }

    $insert->close();
    $conn->close();

} else {
    header("Location: Registration.php");
    exit();
}
?>
