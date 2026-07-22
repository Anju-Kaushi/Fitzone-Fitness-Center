<?php
session_start();

// 🔐 Redirect already logged-in users
if (isset($_SESSION['role'])) {
    switch (strtolower($_SESSION['role'])) {
        case 'admin':
            header("Location: admin-dashboard.php");
            break;
        case 'staff':
            header("Location: staff-dashboard.php");
            break;
        case 'customer':
            header("Location: customer-dashboard.php");
            break;
        default:
            header("Location: index.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register | FitZone Fitness Center</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  
  <!-- AOS CSS -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
  
  <style>
    body {
      /* Gradient background similar to Tailwind's bg-gradient-to-br from-blue-100 to-green-200 */
      background: linear-gradient(to bottom right, #bfdbfe, #bbf7d0);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Roboto', sans-serif;
    }
    .password-toggle {
      cursor: pointer;
      user-select: none;
    }
  </style>
</head>

<body>
  <div class="bg-white p-4 p-md-5 rounded shadow" style="max-width: 400px;" data-aos="zoom-in" data-aos-duration="1000">
    <div class="text-center mb-4">
      <img src="Images/Logo.png" alt="FitZone Logo" class="mx-auto mb-2" style="height: 48px; animation: bounce 2s infinite;">
      <h2 class="fw-bold text-dark">Register</h2>
      <p class="text-muted small">Join FitZone today!</p>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger" role="alert">
        ⚠️ <?= htmlspecialchars($_GET['error']) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success" role="alert">
        ✅ <?= htmlspecialchars($_GET['success']) ?>
      </div>
    <?php endif; ?>

    <form action="register-handler.php" method="POST" class="needs-validation" novalidate>
      <div class="mb-3">
        <label for="fullname" class="form-label small text-secondary">Full Name</label>
        <input id="fullname" name="fullname" type="text" required placeholder="Your name" class="form-control" />
        <div class="invalid-feedback">Please enter your full name.</div>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label small text-secondary">Email</label>
        <input id="email" name="email" type="email" required placeholder="you@example.com" class="form-control" />
        <div class="invalid-feedback">Please enter a valid email.</div>
      </div>
      
      <!-- phone number field (optional) -->
      <div class="mb-3">
        <label for="phone" class="form-label small text-secondary">Phone Number <small class="text-muted">(optional)</small></label>
        <input id="phone" name="phone" type="tel" placeholder="+94 7XX XXX XXX" class="form-control" pattern="^\+?\d{7,15}$" />
        <div class="invalid-feedback">Please enter a valid phone number.</div>
      </div>

      <!-- Force only Customer role using hidden input -->
      <input type="hidden" name="role" value="Customer">

      <div class="mb-3 position-relative">
        <label for="password" class="form-label small text-secondary">Password</label>
        <input id="password" name="password" type="password" required placeholder="••••••" class="form-control pe-5" />
        <span onclick="togglePassword('password', 'eyeIcon1')" class="position-absolute top-50 end-0 translate-middle-y me-3 password-toggle text-secondary">
          <i id="eyeIcon1" class="fas fa-eye"></i>
        </span>
        <div class="invalid-feedback">Please enter a password.</div>
      </div>

      <div class="mb-4 position-relative">
        <label for="confirmPassword" class="form-label small text-secondary">Confirm Password</label>
        <input id="confirmPassword" name="confirm_password" type="password" required placeholder="••••••" class="form-control pe-5" />
        <span onclick="togglePassword('confirmPassword', 'eyeIcon2')" class="position-absolute top-50 end-0 translate-middle-y me-3 password-toggle text-secondary">
          <i id="eyeIcon2" class="fas fa-eye"></i>
        </span>
        <div class="invalid-feedback">Please confirm your password.</div>
      </div>

      <button type="submit" class="btn btn-success w-100 fw-semibold">Register</button>

      <p class="text-center text-muted mt-3 small">
        Already have an account?
        <a href="login.php" class="text-primary text-decoration-none">Login</a>
      </p>
    </form>
  </div>

  <!-- AOS JS -->
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    AOS.init();

    function togglePassword(inputId, iconId) {
      const input = document.getElementById(inputId);
      const icon = document.getElementById(iconId);
      if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
      }
    }

    // Bootstrap validation example
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

  <style>
    /* Bounce animation for logo */
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
  </style>
</body>
</html>
