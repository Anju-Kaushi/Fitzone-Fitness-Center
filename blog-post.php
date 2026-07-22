<?php
include 'db.php';

// Validate ID
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($post_id <= 0) {
    header("Location: Blog.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo '<div class="container py-5"><h2>Post not found</h2><p>This blog post doesn’t exist.</p><a href="Blog.php" class="btn btn-primary">Back to Blog</a></div>';
    include 'footer.php';
    exit();
}

$page = 'blog'; 
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($post['title']) ?> | Fitzone Fitness Center</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f9fafb;
      color: #2c3e50;
      margin: 0;
      padding: 0;
    }

    .hero {
      position: relative;
      background: url('<?= file_exists($post['image']) ? $post['image'] : "Images/Woman stretching at home.png" ?>') no-repeat center center/cover;
      height: 300px;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: #fff;
      padding: 0 15px;
    }

    .hero::before {
      content: "";
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0, 0, 0, 0.45);
      z-index: 1;
    }

    .hero h1 {
      position: relative;
      z-index: 2;
      font-size: 2.75rem;
      font-weight: 700;
      line-height: 1.1;
      letter-spacing: -0.02em;
      margin: 0;
      max-width: 900px;
      text-shadow: 0 2px 6px rgba(0,0,0,0.4);
    }

    .blog-wrapper {
      max-width: 900px;
      margin: -70px auto 60px;
      background-color: #fff;
      border-radius: 20px;
      padding: 45px 40px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.07);
      position: relative;
      z-index: 5;
    }

    .blog-meta {
      font-size: 0.95rem;
      color: #6c757d;
      margin-bottom: 1.2rem;
      font-weight: 500;
    }

    .blog-content {
      font-size: 1.05rem;
      line-height: 1.45; 
      color: #3a3a3a;
      white-space: pre-line;
      letter-spacing: 0.005em;
      font-weight: 400;
    }

    .blog-content p {
      margin-bottom: 0.85rem; 
    }

    .btn-back {
      display: inline-flex;
      align-items: center;
      margin-top: 2.2rem;
      padding: 12px 24px;
      background-color: #fff;
      color: #28a745;
      border: 2px solid #28a745;
      border-radius: 8px;
      font-weight: 700;
      font-size: 1rem;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 6px 15px rgba(40, 167, 69, 0.15);
    }

    .btn-back:hover {
      background-color: #28a745;
      color: #fff;
      box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
    }

    .btn-back svg {
      margin-right: 10px;
      stroke: currentColor;
    }

    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2rem;
      }
      .blog-wrapper {
        margin: -50px 20px 40px;
        padding: 30px 25px;
      }
      .blog-content {
        font-size: 1rem;
      }
      .btn-back {
        padding: 10px 18px;
        font-size: 0.95rem;
      }
    }

    @media (max-width: 480px) {
      .hero h1 {
        font-size: 1.5rem;
      }
      .blog-wrapper {
        margin: -40px 10px 30px;
        padding: 25px 15px;
      }
      .btn-back {
        font-size: 0.9rem;
        padding: 9px 16px;
      }
    }
  </style>
</head>
<body>

  <header class="hero">
    <h1><?= htmlspecialchars($post['title']) ?></h1>
  </header>

  <main class="blog-wrapper">
    <div class="blog-meta">
      By <strong><?= htmlspecialchars($post['author']) ?></strong> • <?= date('F j, Y', strtotime($post['created_at'])) ?>
    </div>

    <article class="blog-content">
      <?= nl2br(htmlspecialchars($post['content'])) ?>
    </article>

    <a href="Blog.php" class="btn-back">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12" />
        <polyline points="12 19 5 12 12 5" />
      </svg>
      Back to Blog
    </a>
  </main>

  <?php include 'footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
