<?php
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine role
$role = strtolower($_SESSION['role'] ?? '');

// Handle action and ID from URL
$action = $_GET['action'] ?? '';
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ===== PROCESS POST (Add or Update) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $class_id = (int)$_POST['class_id'];
    $datetime = $_POST['appointment_datetime'];
    $status = $_POST['status'];

    // Allow both admin and staff to add/edit
    if (!empty($_POST['appointment_id'])) {
        $appointment_id = (int)$_POST['appointment_id'];
        $stmt = $conn->prepare("UPDATE appointments SET user_id=?, class_id=?, appointment_datetime=?, status=? WHERE id=?");
        $stmt->bind_param("iissi", $user_id, $class_id, $datetime, $status, $appointment_id);
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Appointment updated successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to update appointment.";
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO appointments (user_id, class_id, appointment_datetime, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $class_id, $datetime, $status);
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Appointment added successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to add appointment.";
        }
    }
}

// ===== PROCESS DELETE (Admin Only) =====
if ($action === 'delete' && $edit_id) {
    if ($role === 'admin') {
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->bind_param("i", $edit_id);
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Appointment deleted successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to delete appointment.";
        }
    } else {
        $_SESSION['error_msg'] = "You are not authorized to delete appointments.";
    }
    header("Location: " . ($role === 'admin' ? "admin-dashboard.php?page=appointments" : "staff-dashboard.php?page=appointments"));
    exit();
}

// ===== FETCH DATA FOR FORM AND TABLE =====
$users = mysqli_query($conn, "SELECT id, fullname FROM tbluser");
$classes = mysqli_query($conn, "SELECT id, class_name FROM class_schedule");

$edit_data = null;
if ($action === 'edit' && $edit_id) {
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $edit_data = $res->fetch_assoc();
}

$bookings = mysqli_query($conn, "
    SELECT a.id, u.fullname AS user_name, cs.class_name, a.appointment_datetime, a.status
    FROM appointments a
    JOIN tbluser u ON a.user_id = u.id
    JOIN class_schedule cs ON a.class_id = cs.id
    ORDER BY a.appointment_datetime DESC
");
?>

<div class="container my-5">
    <h1 class="mb-4 text-success">
        <?= $action === 'edit' ? 'Edit Appointment' : 'Add Appointment' ?>
    </h1>

    <!-- Success/Error Messages -->
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

    <!-- Appointment Form -->
    <form method="post" class="bg-white p-4 border rounded shadow-sm mb-5">
        <?php if ($edit_data): ?>
            <input type="hidden" name="appointment_id" value="<?= $edit_data['id'] ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="user_id" class="form-label">User</label>
            <select id="user_id" name="user_id" required class="form-select">
                <option value="">-- Select User --</option>
                <?php mysqli_data_seek($users, 0); while ($u = mysqli_fetch_assoc($users)): ?>
                    <option value="<?= $u['id'] ?>" <?= $edit_data && $edit_data['user_id'] == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['fullname']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="class_id" class="form-label">Class</label>
            <select id="class_id" name="class_id" required class="form-select">
                <option value="">-- Select Class --</option>
                <?php mysqli_data_seek($classes, 0); while ($c = mysqli_fetch_assoc($classes)): ?>
                    <option value="<?= $c['id'] ?>" <?= $edit_data && $edit_data['class_id'] == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['class_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="appointment_datetime" class="form-label">Appointment Date & Time</label>
            <input
                type="datetime-local"
                id="appointment_datetime"
                name="appointment_datetime"
                required
                class="form-control"
                value="<?= $edit_data ? date('Y-m-d\TH:i', strtotime($edit_data['appointment_datetime'])) : '' ?>"
            >
        </div>

        <div class="mb-4">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" required class="form-select">
                <option value="Pending" <?= $edit_data && $edit_data['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Confirmed" <?= $edit_data && $edit_data['status'] == 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="Cancelled" <?= $edit_data && $edit_data['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">
            <?= $action === 'edit' ? 'Update' : 'Add' ?> Appointment
        </button>

        <?php if ($action === 'edit'): ?>
            <a href="<?= $role === 'admin' ? 'admin-dashboard.php?page=appointments' : 'staff-dashboard.php?page=appointments' ?>" class="btn btn-secondary ms-3">Cancel</a>
        <?php endif; ?>
    </form>

    <!-- Appointments Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-success">
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Class</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($bookings)): ?>
                    <?php
                    $statusClasses = [
                        'Pending' => 'badge bg-warning text-dark',
                        'Confirmed' => 'badge bg-success',
                        'Cancelled' => 'badge bg-danger',
                    ];
                    $badgeClass = $statusClasses[$row['status']] ?? 'badge bg-secondary';
                    ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['user_name']) ?></td>
                        <td><?= htmlspecialchars($row['class_name']) ?></td>
                        <td><?= date('Y-m-d H:i', strtotime($row['appointment_datetime'])) ?></td>
                        <td><span class="<?= $badgeClass ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                        <td>
                            <!-- Edit allowed for both Admin and Staff -->
                            <a href="<?= $role === 'admin' ? 'admin-dashboard.php' : 'staff-dashboard.php' ?>?page=appointments&action=edit&id=<?= $row['id'] ?>" class="text-primary me-2" title="Edit">&#9998;</a>
                            
                            <!-- Delete allowed for Admin only -->
                            <?php if ($role === 'admin'): ?>
                                <a href="admin-dashboard.php?page=appointments&action=delete&id=<?= $row['id'] ?>"
                                   onclick="return confirm('Are you sure you want to delete this appointment?');"
                                   class="text-danger" title="Delete">&#128465;</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
