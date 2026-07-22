<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

date_default_timezone_set('Asia/Colombo');

// Determine role
$userRole = $_SESSION['role'] ?? 'Staff';
$userRoleLower = strtolower($userRole);

// Set dashboard URL depending on role
$page_url = $userRoleLower === 'admin' ? 'admin-dashboard.php?page=blog' : 'staff-dashboard.php?page=blog';

// Allow only admin and staff
if (!in_array($userRoleLower, ['admin', 'staff'])) {
    header("Location: {$page_url}");
    exit();
}

$edit_data = null;

// Handle Add / Update (Both Admin and Staff)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $created_at = date('Y-m-d H:i:s');
    $image_path = '';

    // Image Upload
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = 'uploads/blog/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $file_name = time() . '_' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $file_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    }

    if (!empty($_POST['id'])) {
        // Update existing
        $id = (int)$_POST['id'];
        if (!empty($image_path)) {
            $stmt = $conn->prepare("UPDATE blog_posts SET title=?, content=?, image=?, created_at=? WHERE id=?");
            $stmt->bind_param("ssssi", $title, $content, $image_path, $created_at, $id);
        } else {
            $stmt = $conn->prepare("UPDATE blog_posts SET title=?, content=?, created_at=? WHERE id=?");
            $stmt->bind_param("sssi", $title, $content, $created_at, $id);
        }
        $stmt->execute();
        $_SESSION['success_message'] = "Blog post updated successfully.";
    } else {
        // Insert new
        $stmt = $conn->prepare("INSERT INTO blog_posts (title, content, image, created_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $content, $image_path, $created_at);
        $stmt->execute();
        $_SESSION['success_message'] = "Blog post added successfully.";
    }

}

// Handle Edit (Both Admin and Staff)
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
}

// Handle Delete (Admin only)
if ($userRoleLower === 'admin' && isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Delete image
    $stmt = $conn->prepare("SELECT image FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data && !empty($data['image']) && file_exists($data['image'])) {
        unlink($data['image']);
    }

    // Delete post
    $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $_SESSION['success_message'] = "Blog post deleted successfully.";
    header("Location: $page_url");
    exit();
}

// Fetch all blogs
$result = $conn->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
?>

<div class="container py-4">

    <!-- Success message display -->
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <h2 class="mb-4"><?= $edit_data ? 'Edit Blog' : 'Add Blog' ?></h2>
    <form action="<?= htmlspecialchars($page_url) ?>" method="POST" enctype="multipart/form-data" class="mb-5">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        <?php endif; ?>
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($edit_data['title'] ?? '') ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" rows="6" class="form-control" required><?= htmlspecialchars($edit_data['content'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Image</label>
            <input type="file" name="image" accept="image/*" class="form-control">
            <?php if (!empty($edit_data['image']) && file_exists($edit_data['image'])): ?>
                <img src="<?= htmlspecialchars($edit_data['image']) ?>" alt="Image" class="img-thumbnail mt-2" style="height: 150px;">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">
            <?= $edit_data ? 'Update Blog' : 'Add Blog' ?>
        </button>
    </form>

    <h2 class="mb-3">All Blog Posts</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th scope="col" style="width: 5%;">#</th>
                    <th scope="col" style="width: 20%;">Title</th>
                    <th scope="col">Content</th>
                    <th scope="col" style="width: 15%;">Image</th>
                    <th scope="col" style="width: 15%;">Created At</th>
                    <th scope="col" style="width: 15%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $index = 1;
                while ($row = $result->fetch_assoc()):
                ?>
                    <tr>
                        <th scope="row"><?= $index++ ?></th>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td>
                            <?= nl2br(htmlspecialchars(strlen($row['content']) > 100 ? substr($row['content'], 0, 100) . '...' : $row['content'])) ?>
                        </td>
                        <td>
                            <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                                <img src="<?= htmlspecialchars($row['image']) ?>" alt="Blog Image" class="img-thumbnail" style="height: 80px;">
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <!-- Edit: Admin and Staff -->
                            <a href="<?= $page_url ?>&edit=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                            <!-- Delete: Admin only -->
                            <?php if ($userRoleLower === 'admin'): ?>
                                <a href="<?= $page_url ?>&delete=<?= $row['id'] ?>" onclick="return confirm('Delete this blog?')" class="btn btn-sm btn-outline-danger">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="6" class="text-center p-4">No blog posts found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
