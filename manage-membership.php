<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db.php';

// Variables
$id = $name = $monthly = $quarterly = $yearly = $discount = $features = $status = '';
$success = $error = '';

// Handle Add or Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $monthly = $_POST['monthly_price'];
    $quarterly = $_POST['quarterly_price'];
    $yearly = $_POST['yearly_price'];
    $discount = $_POST['yearly_discount'];
    $features = $_POST['features'];
    $status = $_POST['status'];

    if (isset($_POST['id']) && $_POST['id'] !== '') {
        // Update
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE membership_plans SET name=?, monthly_price=?, quarterly_price=?, yearly_price=?, yearly_discount=?, features=?, status=? WHERE id=?");
        $stmt->bind_param("sdddsssi", $name, $monthly, $quarterly, $yearly, $discount, $features, $status, $id);
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Membership plan updated successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to update membership plan.";
        }
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO membership_plans (name, monthly_price, quarterly_price, yearly_price, yearly_discount, features, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdddsss", $name, $monthly, $quarterly, $yearly, $discount, $features, $status);
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Membership plan added successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to add membership plan.";
        }
    }
   
}

// Handle Edit
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM membership_plans WHERE id = $edit_id");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        extract($row);
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM membership_plans WHERE id = $delete_id");
    $_SESSION['success_msg'] = "Membership plan deleted successfully!";
    header("Location: admin-dashboard.php?page=membership");
    exit();
}
?>

<!-- Bootstrap CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container my-5">
    <h2 class="mb-4"><?= isset($_GET['edit']) ? 'Edit' : 'Add' ?> Membership Plan</h2>

    <!-- Display Success/Error Messages -->
    <?php if (!empty($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_msg'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>

    <!-- Membership Plan Form -->
    <form method="POST" class="row g-3 mb-5">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

        <div class="col-md-6">
            <label for="name" class="form-label">Plan Name</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($name) ?>" required class="form-control" />
        </div>

        <div class="col-md-6">
            <label for="monthly_price" class="form-label">Monthly Price</label>
            <input type="number" step="0.01" name="monthly_price" id="monthly_price" value="<?= htmlspecialchars($monthly) ?>" required class="form-control" />
        </div>

        <div class="col-md-6">
            <label for="quarterly_price" class="form-label">Quarterly Price</label>
            <input type="number" step="0.01" name="quarterly_price" id="quarterly_price" value="<?= htmlspecialchars($quarterly) ?>" required class="form-control" />
        </div>

        <div class="col-md-6">
            <label for="yearly_price" class="form-label">Yearly Price</label>
            <input type="number" step="0.01" name="yearly_price" id="yearly_price" value="<?= htmlspecialchars($yearly) ?>" required class="form-control" />
        </div>

        <div class="col-md-6">
            <label for="yearly_discount" class="form-label">Yearly Discount</label>
            <input type="text" name="yearly_discount" id="yearly_discount" value="<?= htmlspecialchars($discount) ?>" placeholder="e.g. Save 11%" class="form-control" />
        </div>

        <div class="col-md-6">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <div class="col-12">
            <label for="features" class="form-label">Features</label>
            <textarea name="features" id="features" rows="3" class="form-control"><?= htmlspecialchars($features) ?></textarea>
            <div class="form-text">Use Quotation marks (" ") to separate features.</div>
        </div>

        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">
                <?= isset($_GET['edit']) ? 'Update' : 'Add' ?> Plan
            </button>
            <?php if (isset($_GET['edit'])): ?>
                <a href="admin-dashboard.php?page=membership" class="btn btn-secondary ms-2">Cancel</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Membership Plans Table -->
    <h3 class="mb-3">All Membership Plans</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Monthly</th>
                    <th>Quarterly</th>
                    <th>Yearly</th>
                    <th>Discount</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $plans = $conn->query("SELECT * FROM membership_plans ORDER BY id DESC");
                if ($plans && $plans->num_rows > 0):
                    while ($plan = $plans->fetch_assoc()):
                ?>
                <tr>
                    <td><?= htmlspecialchars($plan['name']) ?></td>
                    <td>LKR <?= number_format($plan['monthly_price'], 2) ?></td>
                    <td>LKR <?= number_format($plan['quarterly_price'], 2) ?></td>
                    <td>LKR <?= number_format($plan['yearly_price'], 2) ?></td>
                    <td><?= htmlspecialchars($plan['yearly_discount']) ?></td>
                    <td><?= ucfirst(htmlspecialchars($plan['status'])) ?></td>
                    <td class="text-center">
                        <a href="admin-dashboard.php?page=membership&edit=<?= $plan['id'] ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                        <a href="admin-dashboard.php?page=membership&delete=<?= $plan['id'] ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this membership plan?');">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">No membership plans found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
