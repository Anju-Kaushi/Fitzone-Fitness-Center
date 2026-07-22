<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Customer') {
    header("Location: login.php");
    exit();
}

$successMessage = '';
$errorMessage = '';

include 'db.php';

$email = $_SESSION['user'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inquiryType = trim($_POST['inquiry_type'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$inquiryType || !$message) {
        $errorMessage = "Please fill in all required fields.";
    } elseif ($phone && !preg_match('/^07\d{8}$/', $phone)) {
        $errorMessage = "Invalid phone number format. Must start with 07 and have 10 digits.";
    } else {
        $stmt = $conn->prepare("INSERT INTO queries (full_name, email, inquiry_type, phone, message, status, submitted_at) VALUES (?, ?, ?, ?, ?, 'Unread', NOW())");

        $full_name = $_SESSION['fullname'] ?? '';

        $stmt->bind_param("sssss", $full_name, $email, $inquiryType, $phone, $message);

        if ($stmt->execute()) {
            $successMessage = "Your query has been submitted successfully.";
        } else {
            $errorMessage = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}

// Fetch customer's queries
$stmt = $conn->prepare("SELECT id, inquiry_type, message, status, response_message, submitted_at FROM queries WHERE email = ? ORDER BY submitted_at DESC");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Submit Query - Fitzone Fitness Center</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .text-truncate-3 {
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

  <div class="container my-4">

    <?php if ($successMessage): ?>
      <div class="alert alert-success text-center mx-auto" style="max-width: 480px;">
        <?= htmlspecialchars($successMessage) ?>
      </div>
    <?php elseif ($errorMessage): ?>
      <div class="alert alert-danger text-center mx-auto" style="max-width: 480px;">
        <?= htmlspecialchars($errorMessage) ?>
      </div>
    <?php endif; ?>

    <div class="card shadow mx-auto mb-5" style="max-width: 480px;">
      <div class="card-body p-4">
        <h1 class="card-title text-center text-primary mb-4">Submit a Query</h1>

        <form action="<?= htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST" id="queryForm" novalidate>
          <div class="mb-3">
            <label for="inquiry_type" class="form-label">Inquiry Type <span class="text-danger">*</span></label>
            <select id="inquiry_type" name="inquiry_type" class="form-select" required>
              <option value="">-- Select Type --</option>
              <option value="General" <?= (($_POST['inquiry_type'] ?? '') === 'General') ? 'selected' : '' ?>>General</option>
              <option value="Membership" <?= (($_POST['inquiry_type'] ?? '') === 'Membership') ? 'selected' : '' ?>>Membership</option>
              <option value="Support" <?= (($_POST['inquiry_type'] ?? '') === 'Support') ? 'selected' : '' ?>>Support</option>
              <option value="Classes" <?= (($_POST['inquiry_type'] ?? '') === 'Classes') ? 'selected' : '' ?>>Classes</option>
            </select>
            <div class="invalid-feedback">Please select an inquiry type.</div>
          </div>

          <div class="mb-3">
            <label for="phone" class="form-label">Phone (optional)</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="07XXXXXXXX" class="form-control" />
            <div class="form-text">Format: 07XXXXXXXX (10 digits, starting with 07)</div>
            <div class="invalid-feedback">Please enter a valid phone number or leave blank.</div>
          </div>

          <div class="mb-4">
            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
            <textarea id="message" name="message" rows="5" class="form-control" placeholder="Write your message here..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            <div class="invalid-feedback">Please enter your message.</div>
          </div>

          <button type="submit" class="btn btn-primary w-100">Send Query</button>
        </form>
      </div>
    </div>

    <h2 class="mb-3">Your Submitted Queries</h2>

    <?php if ($result && $result->num_rows > 0): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Inquiry Type</th>
              <th>Message</th>
              <th>Status</th>
              <th>Response</th>
              <th>Submitted At</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['inquiry_type']) ?></td>
                <td class="text-truncate-3" style="max-width: 300px;" title="<?= htmlspecialchars($row['message']) ?>">
                  <?= nl2br(htmlspecialchars($row['message'])) ?>
                </td>
                <td>
                  <span class="badge
                    <?php
                      switch($row['status']){
                        case 'Unread': echo 'bg-secondary'; break;
                        case 'Read': echo 'bg-primary'; break;
                        case 'Responded': echo 'bg-success'; break;
                        default: echo 'bg-light text-dark';
                      }
                    ?>
                  ">
                    <?= htmlspecialchars($row['status']) ?>
                  </span>
                </td>
                <td style="max-width: 300px;" title="<?= htmlspecialchars($row['response_message']) ?>">
                  <?= nl2br(htmlspecialchars($row['response_message'] ?: '-')) ?>
                </td>
                <td><?= htmlspecialchars($row['submitted_at']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p>You have not submitted any queries yet.</p>
    <?php endif; ?>

  </div>

  <script>
    document.getElementById('queryForm').addEventListener('submit', function(event) {
      var form = event.target;
      var inquiryType = form.inquiry_type.value.trim();
      var message = form.message.value.trim();
      var phone = form.phone.value.trim();
      var phonePattern = /^07\d{8}$/;

      var valid = true;

      form.inquiry_type.classList.remove('is-invalid');
      form.message.classList.remove('is-invalid');
      form.phone.classList.remove('is-invalid');

      if (!inquiryType) {
        form.inquiry_type.classList.add('is-invalid');
        valid = false;
      }

      if (!message) {
        form.message.classList.add('is-invalid');
        valid = false;
      }

      if (phone && !phonePattern.test(phone)) {
        form.phone.classList.add('is-invalid');
        valid = false;
      }

      if (!valid) {
        event.preventDefault();
        event.stopPropagation();
      }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
