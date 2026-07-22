<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include 'db.php';

// Only admin can access
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Initialize messages
$success_message = '';
$error_message = '';

// --- Handle Delete Package first ---
if (isset($_GET['delete_package'])) {
    $package_id = (int)$_GET['delete_package'];
    $stmt = $conn->prepare("DELETE FROM packages WHERE package_id = ?");
    $stmt->bind_param("i", $package_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "✅ Package deleted successfully.";
    } else {
        $_SESSION['error_message'] = "⚠️ Failed to delete package.";
    }
    header("Location: admin-dashboard.php?page=packages");
    exit();
}

// --- Handle Add Package ---
if (isset($_POST['addPackage'])) {
    $trainer_id = $_POST['trainer_id'];
    $sessions = $_POST['sessions'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO packages (trainer_id, sessions, price, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $trainer_id, $sessions, $price, $description);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "✅ Package added successfully.";
    } else {
        $_SESSION['error_message'] = "⚠️ Failed to add package.";
    }
    $stmt->close();
    header("Location: admin-dashboard.php?page=packages");
    exit();
}

// --- Handle Update Package ---
if (isset($_POST['updatePackage'])) {
    $package_id = $_POST['package_id'];
    $trainer_id = $_POST['trainer_id'];
    $sessions = $_POST['sessions'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE packages SET trainer_id = ?, sessions = ?, price = ?, description = ? WHERE package_id = ?");
    $stmt->bind_param("iidsi", $trainer_id, $sessions, $price, $description, $package_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "✅ Package updated successfully.";
    } else {
        $_SESSION['error_message'] = "⚠️ Failed to update package.";
    }
    $stmt->close();
}

// --- Get Package for Editing ---
$editPackage = null;
if (isset($_GET['edit_package'])) {
    $edit_id = (int)$_GET['edit_package'];
    $editResult = $conn->query("SELECT * FROM packages WHERE package_id = $edit_id");
    if ($editResult->num_rows > 0) {
        $editPackage = $editResult->fetch_assoc();
    }
}

// --- Fetch Trainers ---
$trainers = $conn->query("SELECT id, name FROM trainers");

// --- Fetch Packages with Trainer Info ---
$sql = "SELECT p.package_id, t.name, t.bio, t.experience_years, t.certification, t.image, p.sessions, p.price, p.description
        FROM packages p
        JOIN trainers t ON p.trainer_id = t.id
        ORDER BY t.name ASC";
$result = $conn->query($sql);
?>

<!-- Bootstrap CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container my-5">
    <h1 class="mb-4">Trainer Packages</h1>

    <!-- Success & Error Messages -->
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- ADD OR EDIT FORM -->
    <div class="card p-4 shadow-sm mb-5">
        <h2 class="mb-4"><?= $editPackage ? "Edit Package" : "Add New Package" ?></h2>

        <form method="POST" action="admin-dashboard.php?page=packages">
            <?php if ($editPackage): ?>
                <input type="hidden" name="package_id" value="<?= $editPackage['package_id'] ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Trainer</label>
                <select name="trainer_id" required class="form-select">
                    <option value="">Select Trainer</option>
                    <?php
                    $trainers->data_seek(0);
                    while ($t = $trainers->fetch_assoc()):
                    ?>
                        <option value="<?= $t['id'] ?>" <?= ($editPackage && $editPackage['trainer_id'] == $t['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Sessions</label>
                <input type="number" name="sessions" required class="form-control" min="1"
                       value="<?= $editPackage ? $editPackage['sessions'] : '' ?>" />
            </div>

            <div class="mb-3">
                <label class="form-label">Price (LKR)</label>
                <input type="number" step="0.01" name="price" required class="form-control" min="0"
                       value="<?= $editPackage ? $editPackage['price'] : '' ?>" />
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control"><?= $editPackage ? htmlspecialchars($editPackage['description']) : '' ?></textarea>
            </div>

            <button type="submit"
                    name="<?= $editPackage ? 'updatePackage' : 'addPackage' ?>"
                    class="btn <?= $editPackage ? 'btn-warning' : 'btn-primary' ?>">
                <?= $editPackage ? 'Update Package' : 'Add Package' ?>
            </button>

            <?php if ($editPackage): ?>
                <a href="admin-dashboard.php?page=packages" class="btn btn-link ms-3">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- PACKAGE LIST -->
    <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h2 class="card-title h5"><?= htmlspecialchars($row['name']) ?></h2>
                            <p class="card-text fst-italic"><?= nl2br(htmlspecialchars($row['bio'])) ?></p>
                            <p class="text-muted small mb-2">
                                Experience: <?= $row['experience_years'] ?> years | <?= htmlspecialchars($row['certification']) ?>
                            </p>

                            <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                                <img src="<?= htmlspecialchars($row['image']) ?>" alt="Trainer Image" class="img-thumbnail mb-3" style="max-width: 150px;">
                            <?php else: ?>
                                <p class="text-muted fst-italic mb-3">No image available</p>
                            <?php endif; ?>

                            <p class="fw-semibold mb-1">Package: <?= $row['sessions'] ?> Sessions - LKR <?= number_format($row['price'], 2) ?></p>
                            <p class="mb-3"><?= htmlspecialchars($row['description']) ?></p>

                            <a href="admin-dashboard.php?page=packages&edit_package=<?= $row['package_id'] ?>"
                               class="btn btn-sm btn-warning me-2">Edit</a>

                            <a href="admin-dashboard.php?page=packages&delete_package=<?= $row['package_id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this package?')">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">No packages found.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
