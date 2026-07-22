<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine role
$user_role = $_SESSION['role'] ?? 'Staff';
$user_role_lower = strtolower($user_role);

// Set dashboard URL depending on role
$page_url = $user_role_lower === 'admin' ? 'admin-dashboard.php?page=trainers' : 'staff-dashboard.php?page=trainers';

// Handle Add / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $bio = trim($_POST['bio']);
    $experience_years = (int)$_POST['experience_years'];
    $certification = trim($_POST['certification']);
    $edit_id = $_POST['edit_id'] ?? '';
    $image_path = '';

    try {
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $upload_dir = 'uploads/trainers/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $file_name = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            } else {
                $_SESSION['error_msg'] = "Failed to upload image.";
            }
        }

        if (!empty($edit_id)) {
            // Update trainer (both Admin and Staff can edit)
            if ($image_path) {
                $stmt = $conn->prepare("UPDATE trainers SET name=?, bio=?, experience_years=?, certification=?, image=? WHERE id=?");
                $stmt->bind_param("ssissi", $name, $bio, $experience_years, $certification, $image_path, $edit_id);
            } else {
                $stmt = $conn->prepare("UPDATE trainers SET name=?, bio=?, experience_years=?, certification=? WHERE id=?");
                $stmt->bind_param("ssisi", $name, $bio, $experience_years, $certification, $edit_id);
            }
            if ($stmt->execute()) {
                $_SESSION['success_msg'] = "Trainer updated successfully.";
            } else {
                $_SESSION['error_msg'] = "Error updating trainer: " . $stmt->error;
            }
        } else {
            // Insert new trainer (both Admin and Staff)
            $stmt = $conn->prepare("INSERT INTO trainers (name, bio, experience_years, certification, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiss", $name, $bio, $experience_years, $certification, $image_path);
            if ($stmt->execute()) {
                $_SESSION['success_msg'] = "Trainer added successfully.";
            } else {
                $_SESSION['error_msg'] = "Error adding trainer: " . $stmt->error;
            }
        }
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Unexpected error: " . $e->getMessage();
    }

}

// Delete trainer (Admin only)
if (isset($_GET['delete']) && $user_role_lower === 'admin') {
    $id = (int)$_GET['delete'];
    try {
        $result = $conn->query("SELECT image FROM trainers WHERE id = $id");
        $row = $result->fetch_assoc();
        if ($row && !empty($row['image']) && file_exists($row['image'])) {
            unlink($row['image']);
        }
        $stmt = $conn->prepare("DELETE FROM trainers WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Trainer deleted successfully.";
        } else {
            $_SESSION['error_msg'] = "Error deleting trainer: " . $stmt->error;
        }
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Unexpected error: " . $e->getMessage();
    }

    header("Location: $page_url");
    exit();
}

// Edit trainer
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM trainers WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
}

// Fetch all trainers
$trainers = $conn->query("SELECT * FROM trainers ORDER BY id ASC");
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<section class="container my-5">
  <h1 class="mb-4">Manage Trainers</h1>

  <!-- Success / Error Messages -->
  <?php if (!empty($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($_SESSION['success_msg']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_msg']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['error_msg'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($_SESSION['error_msg']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error_msg']); ?>
  <?php endif; ?>

  <!-- Add/Edit Trainer Form -->
  <div class="card mb-5">
    <div class="card-header">
      <h2 class="h5 mb-0"><?= $edit_data ? 'Edit Trainer' : 'Add New Trainer' ?></h2>
    </div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data" action="<?= htmlspecialchars($page_url) ?>">
        <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_data['id'] ?? '') ?>">

        <div class="mb-3">
          <label for="name" class="form-label">Name</label>
          <input id="name" type="text" name="name" required class="form-control" value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>" />
        </div>

        <div class="mb-3">
          <label for="bio" class="form-label">Bio</label>
          <textarea id="bio" name="bio" rows="3" required class="form-control"><?= htmlspecialchars($edit_data['bio'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
          <label for="experience_years" class="form-label">Experience (Years)</label>
          <input id="experience_years" type="number" name="experience_years" required min="0" class="form-control" value="<?= htmlspecialchars($edit_data['experience_years'] ?? '') ?>" />
        </div>

        <div class="mb-3">
          <label for="certification" class="form-label">Certification</label>
          <input id="certification" type="text" name="certification" required class="form-control" value="<?= htmlspecialchars($edit_data['certification'] ?? '') ?>" />
        </div>

        <div class="mb-3">
          <label for="image" class="form-label">Image</label>
          <input id="image" type="file" name="image" accept="image/*" class="form-control" />
          <?php if (!empty($edit_data['image']) && file_exists($edit_data['image'])): ?>
            <img src="<?= htmlspecialchars($edit_data['image']) ?>" class="img-thumbnail mt-2" alt="Trainer Image" style="max-width: 150px;">
          <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary"><?= $edit_data ? 'Update Trainer' : 'Add Trainer' ?></button>
        <?php if ($edit_data): ?>
          <a href="<?= htmlspecialchars($page_url) ?>" class="btn btn-secondary ms-2">Cancel</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <!-- Trainers Table -->
  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle text-nowrap">
      <thead class="table-light">
        <tr>
          <th scope="col">Image</th>
          <th scope="col">Name</th>
          <th scope="col">Bio</th>
          <th scope="col">Experience</th>
          <th scope="col">Certification</th>
          <th scope="col">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $trainers->fetch_assoc()): ?>
          <tr>
            <td>
              <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                <img src="<?= htmlspecialchars($row['image']) ?>" alt="Trainer Image" class="rounded-circle" style="width: 64px; height: 64px; object-fit: cover;">
              <?php else: ?>
                <span class="text-muted fst-italic">No image</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['bio']) ?></td>
            <td><?= htmlspecialchars($row['experience_years']) ?> years</td>
            <td><?= htmlspecialchars($row['certification']) ?></td>
            <td>
              <!-- Edit: Admin and Staff -->
              <a href="<?= $page_url ?>&edit=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>

              <!-- Delete: Admin only -->
              <?php if ($user_role_lower === 'admin'): ?>
                <a href="<?= $page_url ?>&delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this trainer?')">Delete</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</section>
