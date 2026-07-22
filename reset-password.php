<?php
include 'db.php';
session_start();

$token = $_GET['token'] ?? '';
$message = '';
$messageClass = '';
$showForm = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageClass = "alert-danger";
    } else {
        // Validate token
        $stmt = $conn->prepare("SELECT email, expired_at FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $expiredAt = new DateTime($row['expired_at']);
            $now = new DateTime();

            if ($now < $expiredAt) {
                $email = $row['email'];
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Update password
                $stmt = $conn->prepare("UPDATE tbluser SET password = ? WHERE email = ?");
                $stmt->bind_param("ss", $hashedPassword, $email);
                if ($stmt->execute()) {
                    // Delete token after use
                    $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
                    $stmt->bind_param("s", $token);
                    $stmt->execute();

                    $message = "Password has been reset successfully! <a href='login.php' class='alert-link'>Login here</a>.";
                    $messageClass = "alert-success";
                    $showForm = false;
                } else {
                    $message = "Failed to update password.";
                    $messageClass = "alert-danger";
                }
            } else {
                $message = "This password reset link has expired.";
                $messageClass = "alert-danger";
            }
        } else {
            $message = "Invalid token.";
            $messageClass = "alert-danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title>Reset Password - Fitzone</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background-color: #f8f9fa;
}
.card {
    border-radius: 1rem;
}
.card-title {
    font-weight: 600;
}
.input-group-text {
    cursor: pointer;
}
@media (max-width: 576px) {
    .card {
        margin: 0 1rem;
    }
    .form-control, .btn {
        font-size: 0.9rem;
        padding: 0.5rem;
    }
}
</style>
</head>
<body>
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-sm w-100" style="max-width: 500px;">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">Reset Password</h3>

            <?php if ($message): ?>
                <div class="alert <?= $messageClass ?> text-center"><?= $message ?></div>
            <?php endif; ?>

            <?php if ($showForm && $token): ?>
            <form method="post">
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password" required>
                        <span class="input-group-text" onclick="togglePassword('password')">
                            <i class="bi bi-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                        <span class="input-group-text" onclick="togglePassword('confirm_password')">
                            <i class="bi bi-eye"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100">Reset Password</button>
            </form>
            <?php elseif (!$token): ?>
                <div class="alert alert-danger text-center">Missing or invalid token.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    const icon = input.nextElementSibling.querySelector('i');
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = "password";
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>
</body>
</html>
