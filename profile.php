<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

// Check if user is logged in
$email = $_SESSION['user'] ?? null;

if (!$email) {
    echo "<div class='alert alert-danger'>User not found.</div>";
    exit();
}

// Fetch current user data using email
$stmt = $conn->prepare("SELECT id, fullname, email, phone, role, created_at FROM tbluser WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger'>User not found.</div>";
    exit();
}

$user = $result->fetch_assoc();
$user_id = $user['id'];
$success = '';
$error = '';
$shouldLogout = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['fullname']);
    $newEmail = trim($_POST['email']);
    $newPhone = trim($_POST['phone']);
    $newPassword = trim($_POST['password']);

    if (empty($newName) || empty($newEmail) || empty($newPhone)) {
        $error = "Full Name, Email, and Phone are required.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE tbluser SET fullname = ?, email = ?, phone = ?, password = ? WHERE id = ?");
            $updateStmt->bind_param("ssssi", $newName, $newEmail, $newPhone, $hashedPassword, $user_id);
            $shouldLogout = true;
        } else {
            $updateStmt = $conn->prepare("UPDATE tbluser SET fullname = ?, email = ?, phone = ? WHERE id = ?");
            $updateStmt->bind_param("sssi", $newName, $newEmail, $newPhone, $user_id);
        }

        if ($updateStmt->execute()) {
            if ($shouldLogout) {
                session_unset();
                session_destroy();
                echo "<script>alert('Password changed successfully. Please log in again.'); window.location.href='login.php';</script>";
                exit();
            } else {
                $success = "Profile updated successfully.";
                $user['fullname'] = $newName;
                $user['email'] = $newEmail;
                $user['phone'] = $newPhone;
                $_SESSION['fullname'] = $newName;
                $_SESSION['user'] = $newEmail;
            }
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!-- Bootstrap CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container my-4">
  <div class="card shadow-sm mx-auto" style="max-width: 600px;">
    <div class="card-body">
      <h2 class="card-title text-success border-bottom pb-2 mb-4">My Profile</h2>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="d-flex align-items-center mb-4">
        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white fs-3" style="width: 64px; height: 64px;">
          <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
        </div>
        <div class="ms-3">
          <p class="mb-1 fw-semibold"><?= htmlspecialchars($user['fullname']) ?></p>
          <p class="mb-1 text-muted"><?= htmlspecialchars($user['email']) ?></p>
          <p class="mb-1 text-muted"><?= htmlspecialchars($user['phone']) ?></p>
          <p class="mb-0 text-muted text-capitalize"><?= htmlspecialchars($user['role']) ?> | Joined <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
        </div>
      </div>

      <form method="POST" class="needs-validation" novalidate>
        <div class="mb-3">
          <label for="fullname" class="form-label">Full Name</label>
          <input type="text" class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
          <div class="invalid-feedback">Full Name is required.</div>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
          <div class="invalid-feedback">Please enter a valid email address.</div>
        </div>

        <div class="mb-3">
          <label for="phone" class="form-label">Phone Number</label>
          <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
          <div class="invalid-feedback">Phone number is required.</div>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">New Password <small class="text-muted">(optional)</small></label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
        </div>

        <button type="submit" class="btn btn-success">Update Profile</button>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Bootstrap 5 form validation
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
})();
</script>
