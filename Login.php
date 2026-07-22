<?php
session_start();

// Redirect logged-in users directly
if (isset($_SESSION['role'])) {
    switch (strtolower($_SESSION['role'])) {
        case 'admin':
            header("Location: admin-dashboard.php");
            exit();
        case 'staff':
            header("Location: staff-dashboard.php");
            exit();
        case 'customer':
            header("Location: customer-dashboard.php");
            exit();
        default:
            header("Location: index.php");
            exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login | FitZone Fitness Center</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Roboto', sans-serif;
      background: url('Images/Loginbackground.jpg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-container {
      max-width: 400px;
      width: 100%;
      background: rgba(255, 255, 255, 0.9);
      padding: 2rem;
      border-radius: 0.375rem;
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.3);
    }
    .logo {
      display: block;
      margin: 0 auto 1.5rem auto;
      max-height: 60px;
    }
    a.text-primary:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <img src="Images/Logo.png" alt="FitZone Logo" class="logo" />

    <h2 class="text-center fw-bold mb-4">Login to FitZone</h2>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger text-center" role="alert">
        <?= htmlspecialchars($_GET['error']) ?>
      </div>
    <?php endif; ?>

    <form action="login-handler.php" method="POST" class="needs-validation" novalidate>
      <!-- Hidden field to mark desktop login -->
      <input type="hidden" name="from_mobile" value="0">

      <div class="mb-3">
        <label for="email" class="form-label fw-semibold">Email</label>
        <input id="email" name="txtEmail" type="email" required class="form-control" placeholder="you@example.com" />
        <div class="invalid-feedback">
          Please enter a valid email.
        </div>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label fw-semibold">Password</label>
        <input id="password" name="txtPassword" type="password" required class="form-control" placeholder="••••••••" />
        <div class="text-end mt-1">
          <a href="forgot-password.php" class="text-primary small text-decoration-none">Forgot Password?</a>
        </div>
        <div class="invalid-feedback">
          Please enter your password.
        </div>
      </div>

      <button type="submit" class="btn btn-success w-100 fw-semibold">
        Login
      </button>
    </form>

    <p class="mt-4 text-center text-secondary small">
      Don't have an account? <a href="Registration.php" class="text-primary text-decoration-none">Register here</a>
    </p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (() => {
      'use strict'
      const forms = document.querySelectorAll('.needs-validation')
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
    })()
  </script>
</body>
</html>
