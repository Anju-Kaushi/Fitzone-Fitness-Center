<?php
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'];

// ✅ Fetch admin ID from database using email
$stmtAdmin = $conn->prepare("SELECT id FROM tbluser WHERE email = ?");
$stmtAdmin->bind_param("s", $email);
$stmtAdmin->execute();
$resultAdmin = $stmtAdmin->get_result();
$adminData = $resultAdmin->fetch_assoc();
$adminId = $adminData['id'] ?? 0;
$stmtAdmin->close();

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $membershipId = intval($_POST['membership_id']);
    $action = $_POST['action'];

    if (in_array($action, ['Approved', 'Rejected']) && $adminId > 0) {
        $stmt = $conn->prepare("UPDATE user_memberships SET status = ?, approved_by = ? WHERE id = ?");
        $stmt->bind_param("sii", $action, $adminId, $membershipId);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all pending memberships
$sql = "SELECT um.id, u.fullname, u.email, mp.name AS plan_name, um.plan_type, um.payment_slip, um.created_at
        FROM user_memberships um
        JOIN tbluser u ON um.user_id = u.id
        JOIN membership_plans mp ON um.plan_id = mp.id
        WHERE um.status = 'Pending'
        ORDER BY um.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Membership Approval - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container my-5">
    <h2 class="mb-4">Pending Membership Approvals</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Plan</th>
                    <th>Type</th>
                    <th>Slip</th>
                    <th>Requested At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['plan_name']) ?></td>
                        <td><?= htmlspecialchars($row['plan_type']) ?></td>
                        <td><a href="uploads/payment_slips/<?= urlencode($row['payment_slip']) ?>" target="_blank">View</a></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="membership_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="Approved" class="btn btn-success btn-sm">Approve</button>
                                <button type="submit" name="action" value="Rejected" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="alert alert-info">No pending memberships found.</p>
    <?php endif ?>

</body>
</html>

<?php $conn->close(); ?>
