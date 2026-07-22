<?php
session_start();
include 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $email = trim($_POST['txtEmail']);
    $password = $_POST['txtPassword'];
    $fromMobile = isset($_POST['from_mobile']) && $_POST['from_mobile'] == 1; // Detect mobile form

    // Prepare SQL to fetch user
    $stmt = $conn->prepare("SELECT email, password, role, fullname FROM tbluser WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($dbEmail, $dbHashedPassword, $userRole, $fullName);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $dbHashedPassword)) {
            // Store session data
            $_SESSION['user'] = $dbEmail;
            $_SESSION['role'] = $userRole;
            $_SESSION['fullname'] = $fullName;

            // Role-based redirect
            switch (strtolower($userRole)) {
                case 'admin':
                    $redirectUrl = "admin-dashboard.php";
                    break;
                case 'staff':
                    $redirectUrl = "staff-dashboard.php";
                    break;
                case 'customer':
                    $redirectUrl = "customer-dashboard.php";
                    break;
                default:
                    $redirectUrl = "index.php";
            }

            // If from mobile modal, use JS redirect (so it works inside modal without blank page flash)
            if ($fromMobile) {
                echo "<script>window.location.href='$redirectUrl';</script>";
                exit();
            } else {
                header("Location: $redirectUrl");
                exit();
            }

        } else {
            // Incorrect password
            if ($fromMobile) {
                echo "<script>alert('Invalid email or password'); window.history.back();</script>";
                exit();
            } else {
                header("Location: login.php?error=Invalid+email+or+password");
                exit();
            }
        }
    } else {
        // Email not found
        if ($fromMobile) {
            echo "<script>alert('Invalid email or password'); window.history.back();</script>";
            exit();
        } else {
            header("Location: login.php?error=Invalid+email+or+password");
            exit();
        }
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: login.php");
    exit();
}
?>
