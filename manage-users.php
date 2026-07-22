<?php
ob_start();
include 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();


$userRole = strtolower($_SESSION['role'] ?? '');
if (!in_array($userRole, ['admin', 'staff'])) {
    header("Location: admin-dashboard.php?page=dashboard");
    exit();
}


function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function getUserById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM tbluser WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUsers($conn) {
    $result = $conn->query("SELECT * FROM tbluser ORDER BY id ASC");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    return $users;
}

function addUser($conn, $fullname, $email, $phone, $role, $password) {
    $stmt = $conn->prepare("INSERT INTO tbluser (fullname, email, phone, role, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $fullname, $email, $phone, $role, $password);
    return $stmt->execute() ? true : $stmt->error;
}

function updateUser($conn, $id, $fullname, $email, $phone, $role, $password = null) {
    if ($password) {
        $stmt = $conn->prepare("UPDATE tbluser SET fullname=?, email=?, phone=?, role=?, password=? WHERE id=?");
        $stmt->bind_param("sssssi", $fullname, $email, $phone, $role, $password, $id);
    } else {
        $stmt = $conn->prepare("UPDATE tbluser SET fullname=?, email=?, phone=?, role=? WHERE id=?");
        $stmt->bind_param("ssssi", $fullname, $email, $phone, $role, $id);
    }
    return $stmt->execute() ? true : $stmt->error;
}

function deleteUser($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM tbluser WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}


$editUser = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['txtName'] ?? '');
    $email = trim($_POST['txtEmail'] ?? '');
    $phone = trim($_POST['txtPhone'] ?? '');
    $role = $_POST['txtRole'] ?? '';
    $passwordRaw = $_POST['txtPassword'] ?? '';
    $confirmPassword = $_POST['txtConfirmPassword'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($fullname && $email && $phone) {
        if (!empty($passwordRaw) && $passwordRaw !== $confirmPassword) {
            $_SESSION['error_message'] = "⚠️ Passwords do not match.";
        } else {
            $passwordHash = !empty($passwordRaw) ? password_hash($passwordRaw, PASSWORD_DEFAULT) : null;

            if ($userId) {
                $result = updateUser($conn, $userId, $fullname, $email, $phone, $role, $passwordHash);
                $_SESSION['success_message'] = $result === true ? "✅ User updated successfully." : "❌ Update failed: $result";
            } else {
                if (empty($passwordRaw)) {
                    $_SESSION['error_message'] = "⚠️ Please provide a password.";
                } else {
                    $result = addUser($conn, $fullname, $email, $phone, $role, $passwordHash);
                    $_SESSION['success_message'] = $result === true ? "✅ User added successfully." : "❌ Error: $result";
                }
            }
        }
    } else {
        $_SESSION['error_message'] = "⚠️ Please fill in all required fields.";
    }
}


if (isset($_GET['edit'])) {
    $editUser = getUserById($conn, (int)$_GET['edit']);
}

if ($userRole === 'admin' && isset($_GET['delete'])) {
    if (deleteUser($conn, (int)$_GET['delete'])) {
        $_SESSION['success_message'] = "✅ User deleted successfully.";
    } else {
        $_SESSION['error_message'] = "❌ Delete failed.";
    }
}


$users = getUsers($conn);
?>

<div class="container py-4">

    <!-- Success / Error message -->
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= e($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= e($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <h2 class="mb-4"><?= $editUser ? 'Edit User' : 'Add New User' ?></h2>

    <form method="post" class="mb-5" onsubmit="return validatePasswords();">
        <?php if ($editUser): ?>
            <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
        <?php endif; ?>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Full Name</label>
                <input type="text" name="txtName" class="form-control" value="<?= e($editUser['fullname'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="txtEmail" class="form-control" value="<?= e($editUser['email'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input type="text" name="txtPhone" class="form-control" value="<?= e($editUser['phone'] ?? '') ?>" required>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Role</label>
                <select name="txtRole" class="form-select">
                    <option value="Customer" <?= ($editUser['role'] ?? '') === "Customer" ? "selected" : "" ?>>Customer</option>
                    <option value="Staff" <?= ($editUser['role'] ?? '') === "Staff" ? "selected" : "" ?>>Staff</option>
                    <option value="Admin" <?= ($editUser['role'] ?? '') === "Admin" ? "selected" : "" ?>>Admin</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Password</label>
                <input type="password" name="txtPassword" class="form-control" placeholder="<?= $editUser ? 'Leave blank to keep current' : '' ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="txtConfirmPassword" class="form-control" placeholder="<?= $editUser ? 'Leave blank to keep current' : '' ?>">
            </div>
        </div>

        <div>
            <button type="submit" class="btn <?= $editUser ? 'btn-warning' : 'btn-success' ?>" name="<?= $editUser ? 'btnUpdate' : 'btnAdd' ?>">
                <?= $editUser ? 'Update User' : 'Add User' ?>
            </button>
            <?php if ($editUser): ?>
                <a href="admin-dashboard.php?page=users" class="btn btn-secondary ms-2">Cancel</a>
            <?php endif; ?>
        </div>
    </form>

    <h2 class="mb-3">All Users</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $i => $user): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= e($user['fullname']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td><?= e($user['phone']) ?></td>
                            <td><?= e($user['role']) ?></td>
                            <td><?= $user['created_at'] ?></td>
                            <td>
                                <a href="admin-dashboard.php?page=users&edit=<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                <?php if ($userRole === 'admin'): ?>
                                    <a href="admin-dashboard.php?page=users&delete=<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center p-4">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function validatePasswords() {
    var pw = document.querySelector("input[name='txtPassword']").value;
    var cpw = document.querySelector("input[name='txtConfirmPassword']").value;
    if (pw !== cpw) {
        if (pw !== "" || cpw !== "") {
            alert("⚠️ Passwords do not match.");
            return false;
        }
    }
    return true;
}
</script>

<?php ob_end_flush(); ?>
