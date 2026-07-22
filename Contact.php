<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Contact | FitZone Fitness Center</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f9fafb;
      scroll-behavior: smooth;
    }
    .form-group {
      position: relative;
      margin-bottom: 1.75rem;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 1.1rem 0.75rem 0.3rem 0.75rem;
      border: 1.5px solid #d1d5db;
      border-radius: 0.5rem;
      background: #f3f4f6;
      transition: border-color 0.3s ease;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #22c55e;
      background: #fff;
      box-shadow: 0 0 0 0.25rem rgba(34, 197, 94, 0.25);
    }
    .form-group label {
      position: absolute;
      left: 0.75rem;
      top: 1.1rem;
      color: #6b7280;
      font-weight: 500;
      pointer-events: none;
      transition: all 0.2s ease-in-out;
      background: #f9fafb;
      padding: 0 0.25rem;
      border-radius: 0.25rem;
    }
    .form-group input:focus + label,
    .form-group input:not(:placeholder-shown) + label,
    .form-group select:focus + label,
    .form-group select:not([value=""]) + label,
    .form-group textarea:focus + label,
    .form-group textarea:not(:placeholder-shown) + label {
      top: -0.6rem;
      left: 0.5rem;
      font-size: 0.75rem;
      color: #22c55e;
      font-weight: 700;
    }
    select.form-select {
      appearance: none;
      padding-right: 2.5rem;
    }
  </style>
</head>
<body>

<?php include 'header.php'; ?>

<main class="container py-5">
  <h1 class="display-4 fw-bold text-center mb-5 text-dark">Contact Us</h1>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success text-center" role="alert">
      ✅ Thank you! Your message has been sent successfully.
    </div>
  <?php endif; ?>

  <div class="row gx-4 gy-4">
    <!-- Contact Form -->
    <section class="col-md-6 bg-white rounded-3 shadow-sm p-4 p-md-5">
      <h2 class="h3 fw-semibold text-dark mb-4">Send Us a Message</h2>
      <form id="contactForm" method="POST" action="contact-process.php" novalidate>
        <div class="form-group">
          <input type="text" id="name" name="name" placeholder=" " required aria-label="Full Name" autocomplete="name" class="form-control" />
          <label for="name">Full Name</label>
        </div>
        <div class="form-group">
          <input type="email" id="email" name="email" placeholder=" " required aria-label="Email Address" autocomplete="email" class="form-control" />
          <label for="email">Email Address</label>
        </div>
        <div class="form-group">
          <input type="tel" id="phone" name="phone" placeholder=" " pattern="^\+?\d{7,15}$" aria-label="Phone Number" title="Enter valid phone number" autocomplete="tel" class="form-control" />
          <label for="phone">Phone Number</label>
        </div>
        <div class="form-group">
          <select id="inquiry" name="inquiry" required aria-label="Inquiry Type" class="form-select">
            <option value="" disabled selected hidden></option>
            <option value="Membership">Membership</option>
            <option value="Classes">Classes</option>
            <option value="General">General</option>
            <option value="Support">Support</option>
          </select>
          <label for="inquiry">Inquiry Type</label>
        </div>
        <div class="form-group">
          <textarea id="message" name="message" rows="5" placeholder=" " required aria-label="Your Message" class="form-control"></textarea>
          <label for="message">Your Message</label>
        </div>
        <button type="submit" class="btn btn-success w-100 py-3 fs-5 fw-semibold shadow-sm">
          Send Message
        </button>
      </form>
    </section>

    <!-- Google Map -->
    <section class="col-md-6 rounded-3 shadow-sm overflow-hidden" style="height: 24rem;">
      <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.287538596839!2d79.96463191527629!3d7.486083794507565!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae350f2b45242a9%3A0xc3d36e0a9c78fba8!2sKurunegala%2C%20Sri%20Lanka!5e0!3m2!1sen!2sus!4v1689500000000!5m2!1sen!2sus" 
        width="100%" height="100%" style="border:0;" allowfullscreen loading="lazy">
      </iframe>
    </section>
  </div>

  <!-- Address Info -->
  <section class="mt-5 text-center mx-auto px-3" style="max-width: 36rem;">
    <h2 class="h2 fw-bold mb-4 text-dark">Find Us</h2>
    <p class="text-secondary mb-4">
      Have questions or need assistance? Contact us for help with memberships, classes, or general inquiries.
    </p>
    <div class="bg-success bg-opacity-10 border border-success rounded-3 p-4 shadow-sm text-start mx-auto" style="max-width: 26rem;">
      <p class="fw-semibold text-success fs-5 mb-1">FitZone Fitness Center</p>
      <p class="text-success mb-1">No.25, Court Road, Kurunegala, Sri Lanka</p>
      <p class="text-success mb-1">Phone: +94 771 806 004 / +94 766 463 636</p>
      <p class="text-success mb-1">Hotline: +94 377 806 004</p>
      <p class="text-success">Email: <a href="mailto:fitzone@gmail.com" class="text-decoration-underline text-success">fitzone@gmail.com</a></p>
    </div>
  </section>

  <!-- FAQs -->
  <section class="mt-5 mx-auto px-3" style="max-width: 36rem;">
    <h2 class="h3 fw-bold mb-4 text-center text-dark">FAQs & Support</h2>
    <div class="accordion" id="faqAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
            What are your operating hours?
          </button>
        </h2>
        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body text-secondary">
            We are open daily from 5:00 AM to 10:00 PM including weekends.
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
            Do you offer personal training?
          </button>
        </h2>
        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body text-secondary">
            Yes! We offer personal training sessions tailored to your goals.
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
            Can I book classes online?
          </button>
        </h2>
        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body text-secondary">
            Yes, you can use our online portal to view and book classes anytime.
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include 'footer.php'; ?>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Front-End Validation JS -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('contactForm');

  form.addEventListener('submit', function (e) {
    let valid = true;

    // Name
    const name = document.getElementById('name');
    if (name.value.trim() === '') {
      valid = false;
      setError(name, 'Full Name is required');
    } else {
      clearError(name);
    }

    // Email
    const email = document.getElementById('email');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email.value.trim() === '') {
      valid = false;
      setError(email, 'Email is required');
    } else if (!emailRegex.test(email.value.trim())) {
      valid = false;
      setError(email, 'Enter a valid email address');
    } else {
      clearError(email);
    }

    // Phone (optional)
    const phone = document.getElementById('phone');
    const phoneRegex = /^\+?\d{7,15}$/;
    if (phone.value.trim() !== '' && !phoneRegex.test(phone.value.trim())) {
      valid = false;
      setError(phone, 'Enter a valid phone number');
    } else {
      clearError(phone);
    }

    // Inquiry
    const inquiry = document.getElementById('inquiry');
    if (inquiry.value === '') {
      valid = false;
      setError(inquiry, 'Please select an inquiry type');
    } else {
      clearError(inquiry);
    }

    // Message
    const message = document.getElementById('message');
    if (message.value.trim() === '') {
      valid = false;
      setError(message, 'Message cannot be empty');
    } else {
      clearError(message);
    }

    if (!valid) e.preventDefault();
  });

  function setError(element, message) {
    element.classList.add('is-invalid');
    let errorEl = element.nextElementSibling;
    if (!errorEl || !errorEl.classList.contains('invalid-feedback')) {
      errorEl = document.createElement('div');
      errorEl.className = 'invalid-feedback';
      element.parentNode.appendChild(errorEl);
    }
    errorEl.innerText = message;
  }

  function clearError(element) {
    element.classList.remove('is-invalid');
    const errorEl = element.parentNode.querySelector('.invalid-feedback');
    if (errorEl) errorEl.remove();
  }
});
</script>

</body>
</html>
