<?php
include 'db.php';
session_start();

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$resetMessage = '';

if (isset($_POST['btnSubmit'])) {
    $email = $_POST['txtEmail'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM tbluser WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $rawToken = random_bytes(16);
        $token = base64url_encode($rawToken);
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Insert token
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expired_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires);
        $stmt->execute();

        $link = "reset-password.php?token=$token"; 

        $resetMessage = "<div class='alert alert-success mt-4 text-break'>
            <strong>Reset Link:</strong> <br>
            <a href='$link' class='text-decoration-underline' target='_self'>$link</a>
        </div>";
    } else {
        $resetMessage = "<div class='alert alert-danger mt-4'>
            Email not found in our records.
        </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title>Forgot Password - Fitzone</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

<style>
body {
    background-color: #f8f9fa;
}
.container-card {
    max-width: 400px;
    width: 100%;
    padding: 2rem;
    border-radius: 1rem;
    background-color: #ffffff;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}
h2 {
    font-weight: 600;
}
@media (max-width: 576px) {
    .container-card {
        padding: 1rem;
        margin: 0 1rem;
    }
    .form-control, .btn {
        font-size: 0.9rem;
        padding: 0.5rem;
    }
}
</style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

<div class="container-card text-center">
    <h2 class="mb-4 text-dark">Forgot Password</h2>
    <form method="post">
        <div class="mb-3 text-start">
            <label for="txtEmail" class="form-label text-secondary">Email Address</label>
            <input type="email" name="txtEmail" id="txtEmail" required class="form-control" placeholder="Enter your email" />
        </div>
        <button type="submit" name="btnSubmit" class="btn btn-primary w-100 fw-semibold">Send Reset Link</button>
    </form>

    <?= $resetMessage ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
