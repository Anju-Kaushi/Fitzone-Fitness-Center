<!-- Bootstrap CSS and Icons (place in <head> or before footer) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Footer Styles -->
<style>
  footer a:hover {
    color: #0d6efd !important;
  }

  footer input::placeholder,
  footer textarea::placeholder {
    color: rgba(255, 255, 255, 0.7);
  }

  footer input,
  footer textarea {
    box-shadow: none !important;
  }

  .footer-divider {
    border-top: 1px solid rgba(255, 255, 255, 0.15);
  }

  .social-icon:hover {
    transform: scale(1.1);
    transition: 0.3s ease;
  }

  .footer-icon {
    width: 22px;
    height: 22px;
  }

  @media (max-width: 576px) {
    footer .col-md-3, footer .col-md-6 {
      text-align: center !important;
    }
    footer .d-flex.flex-column.gap-2 {
      align-items: center !important;
    }
    footer input, footer textarea, footer button {
      font-size: 0.9rem;
    }
  }
</style>

<!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-4 mt-5 border-top border-secondary">
  <div class="container">
    <div class="row gy-5">

      <!-- Logo & Social -->
      <div class="col-md-3 text-center text-md-start">
        <img src="Images/Logo.png" alt="FitZone Logo" class="mb-3 bg-white p-2 rounded shadow-sm" style="height: 70px;">
        <p class="small text-secondary">Follow us on</p>
        <div class="d-flex flex-column gap-2">
          <a href="https://www.facebook.com/FitzoneFitnessCenter" target="_blank" class="text-white text-decoration-none d-flex align-items-center gap-2 social-icon">
            <i class="bi bi-facebook footer-icon"></i> <span>Facebook</span>
          </a>
          <a href="https://www.instagram.com/fitzonefitnesscenter" target="_blank" class="text-white text-decoration-none d-flex align-items-center gap-2 social-icon">
            <i class="bi bi-instagram footer-icon"></i> <span>Instagram</span>
          </a>
          <a href="https://www.tiktok.com/@fitzonefitnesscenter" target="_blank" class="text-white text-decoration-none d-flex align-items-center gap-2 social-icon">
            <i class="bi bi-tiktok footer-icon"></i> <span>TikTok</span>
          </a>
        </div>
      </div>

      <!-- Sitemap -->
      <div class="col-md-3">
        <h5 class="fw-semibold mb-3">Quick Links</h5>
        <ul class="list-unstyled small">
          <li><a href="index.php" class="text-white text-decoration-none d-block py-1">🏠 Home</a></li>
          <li><a href="Aboutus.php" class="text-white text-decoration-none d-block py-1">ℹ️ About Us</a></li>
          <li><a href="Services.php" class="text-white text-decoration-none d-block py-1">🛠 Services</a></li>
          <li><a href="Schedule.php" class="text-white text-decoration-none d-block py-1">📅 Schedules</a></li>
          <li><a href="Trainers.php" class="text-white text-decoration-none d-block py-1">👥 Trainers</a></li>
          <li><a href="Membership.php" class="text-white text-decoration-none d-block py-1">🎫 Membership</a></li>
          <li><a href="Blog.php" class="text-white text-decoration-none d-block py-1">📝 Blog</a></li>
          <li><a href="Contact.php" class="text-white text-decoration-none d-block py-1">📞 Contact</a></li>
        </ul>
      </div>

      <!-- Contact Form -->
      <div class="col-md-6">
        <h5 class="fw-semibold mb-3">Send Us a Message</h5>
        <form method="post" action="#contactForm" id="contactForm" novalidate>
          <div class="mb-3">
            <input type="text" name="name" class="form-control bg-secondary text-white border-0" placeholder="Full Name" required>
          </div>
          <div class="mb-3">
            <input type="email" name="email" class="form-control bg-secondary text-white border-0" placeholder="Email Address" required>
          </div>
          <div class="mb-3">
            <input type="tel" name="phone" class="form-control bg-secondary text-white border-0" placeholder="Phone Number" required>
          </div>
          <div class="mb-3">
            <textarea name="message" class="form-control bg-secondary text-white border-0" rows="4" placeholder="Your Message" required></textarea>
          </div>
          <button type="submit" class="btn btn-success w-100">Submit</button>
        </form>

        <!-- PHP Contact Logic -->
        <?php
        include 'db.php';
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
          $full_name = trim($_POST["name"]);
          $email = trim($_POST["email"]);
          $phone = trim($_POST["phone"]);
          $message = trim($_POST["message"]);

          if (empty($full_name) || empty($email) || empty($phone) || empty($message)) {
            echo "<p class='mt-3 text-danger fw-semibold'>⚠️ Please fill out all fields before submitting.</p>";
          } else {
            $inquiry_type = '';
            $stmt = $conn->prepare("INSERT INTO queries (full_name, email, phone, inquiry_type, message, status, submitted_at) VALUES (?, ?, ?, ?, ?, 'unread', NOW())");
            $stmt->bind_param("sssss", $full_name, $email, $phone, $inquiry_type, $message);

            if ($stmt->execute()) {
              echo "<p class='mt-3 text-success fw-semibold'>✅ Thank you, $full_name! Your message has been received.</p>";
            } else {
              echo "<p class='mt-3 text-danger fw-semibold'>⚠️ Sorry, something went wrong. Please try again later.</p>";
            }
            $stmt->close();
          }
        }
        ?>
      </div>
    </div>

    <!-- Divider -->
    <hr class="footer-divider my-4">

    <!-- Footer Bottom -->
    <div class="text-center text-secondary small">
      <p class="mb-1">📍 FitZone Fitness Center | No.25, Court Road, Kurunegala</p>
      <p class="mb-1">📞 0377 806 004 | 📧 fitzone@gmail.com</p>
      <p class="mb-0">© <?= date("Y"); ?> FitZone Fitness Center. All Rights Reserved.</p>
    </div>
  </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
