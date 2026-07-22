<?php
session_start();
include 'db.php';

// Redirect if not logged in or not a Customer
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Customer') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'] ?? '';
$fullname = $_SESSION['fullname'] ?? 'Customer';
$currentPage = strtolower($_GET['page'] ?? 'customer-home');

// Fetch user data (optional)
$stmt = $conn->prepare("SELECT fullname, email, created_at FROM tbluser WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->num_rows > 0 ? $result->fetch_assoc() : null;
$error = $userData ? '' : "User not found.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Customer Dashboard | FitZone Fitness Center</title>
</head>

<body class="bg-light" style="font-family: 'Roboto', sans-serif;">

<?php include 'header.php'; ?>

<div class="d-flex min-vh-100">


  <?php include 'customer-header.php'; ?>

  <!-- Main Content -->
  <main class="flex-grow-1 overflow-auto p-4 bg-white rounded shadow w-100">

    <?php 
    if (isset($_GET['booking']) && $_GET['booking'] === 'success') {
        echo "<div class='alert alert-success mb-4'>Class booked successfully!</div>";
    }

    switch ($currentPage) {
        case 'customer-home':
            include 'customer-home.php';
            break;

        case 'profile-view':
            include 'profile.php';
            break;

        case 'register-class':
            include 'book-class.php';
            break;

        case 'membership':
            include 'plans.php';
            break;

        case 'submit-query':
            include 'submit-query.php';
            break;

        case 'blogs':
            $blogStmt = $conn->prepare("SELECT id, title, content, author, created_at, image FROM blog_posts ORDER BY created_at DESC");
            $blogStmt->execute();
            $blogResult = $blogStmt->get_result();

            echo "<h2 class='h4 text-success mb-4'>Latest Blog Posts</h2>";
            echo "<div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4'>";

            if ($blogResult->num_rows > 0) {
                while ($post = $blogResult->fetch_assoc()) {
                    $author = !empty($post['author']) ? htmlspecialchars($post['author']) : "Admin";
                    $title = htmlspecialchars($post['title']);
                    $createdAt = date('F j, Y', strtotime($post['created_at']));
                    $contentPreview = htmlspecialchars(mb_substr($post['content'], 0, 100)) . (mb_strlen($post['content']) > 100 ? '...' : '');
                    $imgSrc = (!empty($post['image']) && file_exists($post['image'])) ? htmlspecialchars($post['image']) : 'Images/default.png';

                    echo "
                    <div class='col'>
                      <div class='card h-100 border-0 shadow-sm'>
                        <div class='ratio ratio-16x9'>
                          <img src='{$imgSrc}' class='card-img-top object-fit-cover rounded-top' alt='Blog Image'>
                        </div>
                        <div class='card-body d-flex flex-column'>
                          <h5 class='card-title text-success'>{$title}</h5>
                          <p class='card-subtitle text-muted small mb-2'>By {$author} on {$createdAt}</p>
                          <p class='card-text flex-grow-1'>{$contentPreview}</p>
                          <a href='blog-post.php?id={$post['id']}' class='btn btn-sm btn-outline-success mt-auto'>Read More</a>
                        </div>
                      </div>
                    </div>";
                }
            } else {
                echo "<p class='text-muted'>No blog posts found.</p>";
            }

            echo "</div>";
            break;

        default:
            include 'customer-home.php';
            break;
    }
    ?>
  </main>
</div>

<?php include 'customer-footer.php'; ?>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
