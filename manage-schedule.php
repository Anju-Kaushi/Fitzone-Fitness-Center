<?php
ob_start();
include 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Normalize role for case-insensitive check
$role = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : null;
if (!in_array($role, ['admin', 'staff'])) {
    echo "<p class='text-danger text-center mt-4'>⛔ Access denied.</p>";
    exit;
}

// Handle POST (Add/Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = trim($_POST['class_name']);
    $trainer_id = intval($_POST['trainer_id']);
    $time = $_POST['time'];
    $day = $_POST['day'];
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : null;

    if ($edit_id) {
        $stmt = $conn->prepare("UPDATE class_schedule SET class_name=?, trainer_id=?, time=?, day=? WHERE id=?");
        $stmt->bind_param("sissi", $class_name, $trainer_id, $time, $day, $edit_id);
        $_SESSION['success_msg'] = $stmt->execute() ? "✅ Class updated successfully." : "⚠️ Failed to update class.";
    } else {
        $stmt = $conn->prepare("INSERT INTO class_schedule (class_name, trainer_id, time, day) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $class_name, $trainer_id, $time, $day);
        $_SESSION['success_msg'] = $stmt->execute() ? "✅ Class added successfully." : "⚠️ Failed to add class.";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM class_schedule WHERE id = ?");
    $stmt->bind_param("i", $id);
    $_SESSION['success_msg'] = $stmt->execute() ? "✅ Class deleted successfully." : "⚠️ Failed to delete class.";

    // Redirect to correct dashboard
    $redirectPage = ($role === 'admin') ? 'admin-dashboard.php?page=schedule' : 'staff-dashboard.php?page=schedule';
    header("Location: $redirectPage");
    exit();
}

// Fetch trainers for dropdown
$trainers = $conn->query("SELECT id, name FROM trainers ORDER BY name");

// Fetch schedule with trainer names
$schedule = $conn->query("
    SELECT cs.*, t.name AS trainer_name
    FROM class_schedule cs
    LEFT JOIN trainers t ON cs.trainer_id = t.id
    ORDER BY FIELD(cs.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), cs.time
");

// Fetch edit data if editing
$editData = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM class_schedule WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $editData = $res->fetch_assoc();
    }
}
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<section class="container my-5">
    <h1 class="mb-4 text-primary">📅 Class Schedule</h1>

    <!-- Success Message -->
    <?php if (!empty($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <!-- Add/Edit Form -->
    <div class="card mb-5 shadow-sm">
        <div class="card-header">
            <h2 class="h5 mb-0"><?= $editData ? "✏️ Edit Class" : "➕ Add New Class" ?></h2>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <input type="hidden" name="edit_id" value="<?= htmlspecialchars($editData['id'] ?? '') ?>">

                <div class="col-md-6">
                    <label for="class_name" class="form-label">Class Name</label>
                    <input type="text" id="class_name" name="class_name" class="form-control" required
                           value="<?= htmlspecialchars($editData['class_name'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label for="trainer_id" class="form-label">Trainer</label>
                    <select id="trainer_id" name="trainer_id" class="form-select" required>
                        <option value="">Select Trainer</option>
                        <?php if ($trainers && $trainers->num_rows > 0) $trainers->data_seek(0); ?>
                        <?php while ($row = $trainers->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>" <?= (isset($editData['trainer_id']) && $editData['trainer_id'] == $row['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="time" class="form-label">Time</label>
                    <input type="time" id="time" name="time" class="form-control" required
                           value="<?= htmlspecialchars($editData['time'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label for="day" class="form-label">Day</label>
                    <select id="day" name="day" class="form-select" required>
                        <option value="">Select Day</option>
                        <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day): ?>
                            <option value="<?= $day ?>" <?= (isset($editData['day']) && $editData['day'] === $day) ? 'selected' : '' ?>>
                                <?= $day ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">
                        <?= $editData ? '<i class="bi bi-pencil-square me-1"></i>Update Class' : '<i class="bi bi-plus-circle me-1"></i>Add Class' ?>
                    </button>
                    <?php if ($editData): ?>
                        <a href="<?= ($role === 'admin') ? 'admin-dashboard.php?page=schedule' : 'staff-dashboard.php?page=schedule' ?>" class="btn btn-secondary ms-2">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedule Table -->
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-bordered table-hover align-middle text-nowrap mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">Class</th>
                    <th scope="col">Trainer</th>
                    <th scope="col">Time</th>
                    <th scope="col">Day</th>
                    <th scope="col" style="width: 130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($schedule && $schedule->num_rows > 0): ?>
                    <?php while ($row = $schedule->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['class_name']) ?></td>
                            <td><?= htmlspecialchars($row['trainer_name'] ?? 'N/A') ?></td>
                            <td><?= date("h:i A", strtotime($row['time'])) ?></td>
                            <td><?= htmlspecialchars($row['day']) ?></td>
                            <td>
                                <!-- Edit for both Admin and Staff -->
                                <a href="<?= ($role === 'admin') ? 'admin-dashboard.php?page=schedule&edit=' . $row['id'] : 'staff-dashboard.php?page=schedule&edit=' . $row['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <!-- Delete for both Admin and Staff -->
                                <a href="<?= ($role === 'admin') ? 'admin-dashboard.php?page=schedule&delete=' . $row['id'] : 'staff-dashboard.php?page=schedule&delete=' . $row['id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Delete this class?')"
                                   title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No classes scheduled.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
