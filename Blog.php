<?php
include 'db.php';
include 'header.php';

$result = mysqli_query($conn, "SELECT * FROM blog_posts ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Blog | FitZone Fitness Center</title>

  <!-- Google Fonts Roboto -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f3f4f6;
      color: #2c3e50;
      margin: 0;
      padding: 0;
    }

    main.container {
      max-width: 1140px;
      padding-top: 3rem;
      padding-bottom: 3rem;
    }

    h1.page-title {
      font-weight: 700;
      font-size: 2.8rem;
      margin-bottom: 3rem;
      color: #1a1a1a;
      text-align: center;
      letter-spacing: 1px;
    }

    .blog-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 2.5rem;
    }

    .blog-card {
      background: #fff;
      border-radius: 0.7rem;
      box-shadow: 0 4px 15px rgb(0 0 0 / 0.1);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .blog-card:hover {
      box-shadow: 0 8px 30px rgb(0 0 0 / 0.15);
      transform: translateY(-6px);
    }

    .blog-image-wrapper {
      width: 100%;
      height: 200px;
      overflow: hidden;
      border-radius: 0.7rem 0.7rem 0 0;
    }

    .blog-image-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
      display: block;
    }

    .blog-card:hover .blog-image-wrapper img {
      transform: scale(1.07);
    }

    .blog-content {
      flex-grow: 1;
      padding: 1.8rem 2rem 2rem 2rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .blog-title {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
      color: #27ae60;
      line-height: 1.2;
    }

    .blog-excerpt {
      flex-grow: 1;
      font-size: 1rem;
      color: #555;
      line-height: 1.5;
      margin-bottom: 1.6rem;
      overflow: hidden;
      max-height: 4.5em;
      text-overflow: ellipsis;
    }

    .blog-meta {
      font-size: 0.85rem;
      color: #999;
      font-style: italic;
      margin-bottom: 1.5rem;
    }

    .read-more-btn {
      align-self: flex-start;
      border: 2px solid #27ae60;
      background-color: transparent;
      color: #27ae60;
      padding: 0.45rem 1.25rem;
      font-weight: 600;
      border-radius: 0.4rem;
      transition: all 0.3s ease;
      text-decoration: none;
    }

    .read-more-btn:hover {
      background-color: #27ae60;
      color: white;
      text-decoration: none;
    }

    @media (max-width: 575.98px) {
      .blog-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
      }

      main.container {
        padding-left: 1rem;
        padding-right: 1rem;
      }
    }
  </style>
</head>
<body>
  <main class="container">
    <h1 class="page-title">Fitness Tips, Recipes & Success Stories</h1>

    <section class="blog-grid" aria-label="Blog Posts">
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <article class="blog-card" aria-labelledby="post-title-<?= (int)$row['id'] ?>">
          <div class="blog-image-wrapper">
            <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
              <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>" loading="lazy" />
            <?php else: ?>
              <img src="Images/Woman stretching at home.png" alt="Default blog image" loading="lazy" />
            <?php endif; ?>
          </div>

          <div class="blog-content">
            <h2 id="post-title-<?= (int)$row['id'] ?>" class="blog-title"><?= htmlspecialchars($row['title']) ?></h2>
            <p class="blog-excerpt"><?= htmlspecialchars(substr($row['content'], 0, 150)) ?>...</p>
            <div class="blog-meta">
              By <?= htmlspecialchars($row['author']) ?> — <?= date('d M, Y', strtotime($row['created_at'])) ?>
            </div>
            <a href="blog-post.php?id=<?= (int)$row['id'] ?>" class="read-more-btn" aria-label="Read more about <?= htmlspecialchars($row['title']) ?>">Read More</a>
          </div>
        </article>
      <?php endwhile; ?>
    </section>
  </main>

  <?php include 'footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
