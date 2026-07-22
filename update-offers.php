<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

date_default_timezone_set('Asia/Colombo');

// Determine role
$userRole = $_SESSION['role'] ?? 'staff';
$userRoleLower = strtolower($userRole);

// Set dashboard URL based on role
$page_url = $userRoleLower === 'admin' ? 'admin-dashboard.php?page=offers' : 'staff-dashboard.php?page=offers';

// Allow only admin and staff
if (!in_array($userRoleLower, ['admin', 'staff'])) {
    header("Location: $page_url");
    exit();
}

$edit_data = null;

// --- Handle Delete (Admin only) ---
if (isset($_GET['delete']) && $userRoleLower === 'admin') {
    $id = (int)$_GET['delete'];

    // Delete image if exists
    $stmt = $conn->prepare("SELECT image FROM offers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if ($data && !empty($data['image']) && file_exists($data['image'])) {
        unlink($data['image']);
    }

    $stmt = $conn->prepare("DELETE FROM offers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $_SESSION['success_message'] = $stmt->execute() ? "✅ Offer deleted successfully." : "⚠️ Failed to delete offer.";

    header("Location: $page_url");
    exit();
}

// --- Handle Add / Update (Both Admin and Staff) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $valid_until = $_POST['valid_until'];
    $image_path = '';
    $edit_id = $_POST['edit_id'] ?? '';

    // Image upload
    if (!empty($_FILES['image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 2 * 1024 * 1024;
        $upload_dir = 'uploads/offers/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $file_tmp = $_FILES['image']['tmp_name'];
        $file_type = mime_content_type($file_tmp);
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($file_type, $allowed_types) && $_FILES['image']['size'] <= $max_size) {
            $file_name = time() . '_' . uniqid() . '.' . $file_ext;
            $image_path = $upload_dir . $file_name;
            if (!move_uploaded_file($file_tmp, $image_path)) {
                $_SESSION['error_message'] = "⚠️ Failed to upload image.";
                header("Location: $page_url");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "⚠️ Invalid image. Only JPG and PNG under 2MB allowed.";
            header("Location: $page_url");
            exit();
        }
    }

    if (!empty($edit_id)) {
        // Update
        if (!empty($image_path)) {
            $stmt = $conn->prepare("UPDATE offers SET title=?, description=?, valid_until=?, image=? WHERE id=?");
            $stmt->bind_param("ssssi", $title, $description, $valid_until, $image_path, $edit_id);
        } else {
            $stmt = $conn->prepare("UPDATE offers SET title=?, description=?, valid_until=? WHERE id=?");
            $stmt->bind_param("sssi", $title, $description, $valid_until, $edit_id);
        }
        $_SESSION['success_message'] = $stmt->execute() ? "✅ Offer updated successfully." : "⚠️ Failed to update offer.";
    } else {
        // Add
        $stmt = $conn->prepare("INSERT INTO offers (title, description, valid_until, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $description, $valid_until, $image_path);
        $_SESSION['success_message'] = $stmt->execute() ? "✅ Offer added successfully." : "⚠️ Failed to add offer.";
    }

}

// --- Fetch Edit Data ---
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM offers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

// --- Fetch All Offers ---
$offers = $conn->query("SELECT * FROM offers ORDER BY id DESC");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container py-4">

    <!-- Alerts -->
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Add/Edit Form -->
    <h2 class="mb-4"><?= $edit_data ? 'Edit Offer' : 'Add Offer' ?></h2>
    <form action="<?= htmlspecialchars($page_url) ?>" method="POST" enctype="multipart/form-data" class="mb-5">
        <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_data['id'] ?? '') ?>">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($edit_data['title'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" rows="4" class="form-control" required><?= htmlspecialchars($edit_data['description'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Valid Until</label>
            <input type="date" name="valid_until" class="form-control" required value="<?= htmlspecialchars($edit_data['valid_until'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Image (PNG, JPG - Max 2MB)</label>
            <input type="file" name="image" class="form-control" accept="image/png, image/jpeg">
            <?php if (!empty($edit_data['image'])): ?>
                <img src="<?= htmlspecialchars($edit_data['image']) ?>" class="img-thumbnail mt-2" style="height: 150px;">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary"><?= $edit_data ? 'Update Offer' : 'Add Offer' ?></button>
        <?php if ($edit_data): ?>
            <a href="<?= $page_url ?>" class="btn btn-secondary ms-2">Cancel</a>
        <?php endif; ?>
    </form>

    <!-- Offers Table -->
    <h2 class="mb-3">All Offers</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Valid Until</th>
                    <th style="width: 20%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($offers->num_rows > 0): ?>
                    <?php while ($row = $offers->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                                    <img src="<?= htmlspecialchars($row['image']) ?>" class="img-thumbnail" style="height: 60px;">
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['description'])) ?></td>
                            <td><?= htmlspecialchars($row['valid_until']) ?></td>
                            <td>
                                <a href="<?= $page_url ?>&edit=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                <?php if ($userRoleLower === 'admin'): ?>
                                    <a href="<?= $page_url ?>&delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this offer?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center p-4">No offers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
