<?php
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_role = strtolower($_SESSION['role'] ?? 'staff');

// ===== HANDLE DELETE (Admin Only) =====
if (isset($_GET['delete']) && $user_role === 'admin') {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM queries WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Query deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Failed to delete query.";
    }
    $stmt->close();
    header("Location: admin-dashboard.php?page=manage-queries");
    exit();
}

// ===== HANDLE RESPOND TO QUERY (Admin & Staff) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respond_submit'])) {
    $query_id = intval($_POST['query_id']);
    $status = $_POST['status'];
    $response_message = trim($_POST['response_message']);

    $stmt = $conn->prepare("UPDATE queries SET status = ?, response_message = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $response_message, $query_id);
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Response saved successfully!";
    } else {
        $_SESSION['error_msg'] = "Failed to save response.";
    }
    $stmt->close();
   
}

// ===== FETCH ALL QUERIES =====
$query_result = $conn->query("SELECT * FROM queries ORDER BY submitted_at DESC");
?>

<!-- Bootstrap CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

<div class="container mt-4">

  <!-- Success/Error Messages -->
  <?php if (!empty($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?= htmlspecialchars($_SESSION['success_msg']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_msg']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['error_msg'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <?= htmlspecialchars($_SESSION['error_msg']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error_msg']); ?>
  <?php endif; ?>

  <h1 class="mb-4">Manage Queries</h1>

  <div class="table-responsive bg-white shadow rounded p-3">
    <table class="table table-hover table-bordered align-middle text-sm">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Type</th>
          <th>Message</th>
          <th>Status</th>
          <th>Submitted</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($query_result && $query_result->num_rows > 0): ?>
          <?php while ($row = $query_result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['full_name']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['phone']) ?></td>
              <td><?= htmlspecialchars($row['inquiry_type']) ?></td>
              <td class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($row['message']) ?>">
                <?= htmlspecialchars($row['message']) ?>
              </td>
              <td>
                <span class="badge
                  <?php
                    switch($row['status']){
                      case 'Unread': echo 'bg-secondary'; break;
                      case 'Read': echo 'bg-primary'; break;
                      case 'Responded': echo 'bg-success'; break;
                      default: echo 'bg-light text-dark';
                    }
                  ?>
                ">
                  <?= htmlspecialchars($row['status']) ?>
                </span>
              </td>
              <td><?= $row['submitted_at'] ?></td>
              <td class="text-center">
                <button
                  type="button"
                  class="btn btn-link btn-sm"
                  onclick='viewQuery(<?= json_encode($row) ?>)'>
                  View
                </button>

                <button
                  type="button"
                  class="btn btn-link btn-sm text-success"
                  onclick='respondQuery(<?= json_encode($row) ?>)'>
                  Respond
                </button>

                <?php if ($user_role === 'admin'): ?>
                  <a href="admin-dashboard.php?page=manage-queries&delete=<?= $row['id'] ?>"
                     onclick="return confirm('Are you sure you want to delete this query?')"
                     class="btn btn-link btn-sm text-danger">Delete</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="9" class="text-center">No queries found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewModalLabel">Query Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewContent"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Respond Modal -->
<div class="modal fade" id="respondModal" tabindex="-1" aria-labelledby="respondModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="respondModalLabel">Respond to Query</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="query_id" id="respondQueryId" />
          <div class="mb-3">
            <label for="respondStatus" class="form-label">Status</label>
            <select name="status" id="respondStatus" required class="form-select">
              <option value="Unread">Unread</option>
              <option value="Read">Read</option>
              <option value="Responded">Responded</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="respondMessage" class="form-label">Response Message</label>
            <textarea name="response_message" id="respondMessage" rows="4" class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="respond_submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
  const respondModal = new bootstrap.Modal(document.getElementById('respondModal'));

  function viewQuery(data) {
    const html = `
      <p><strong>Name:</strong> ${escapeHtml(data.full_name)}</p>
      <p><strong>Email:</strong> ${escapeHtml(data.email)}</p>
      <p><strong>Phone:</strong> ${escapeHtml(data.phone)}</p>
      <p><strong>Inquiry:</strong> ${escapeHtml(data.inquiry_type)}</p>
      <p><strong>Message:</strong> ${escapeHtml(data.message)}</p>
      <p><strong>Status:</strong> ${escapeHtml(data.status)}</p>
      ${data.response_message ? `<p><strong>Response:</strong> ${escapeHtml(data.response_message)}</p>` : ''}
    `;
    document.getElementById('viewContent').innerHTML = html;
    viewModal.show();
  }

  function respondQuery(data) {
    document.getElementById('respondQueryId').value = data.id;
    document.getElementById('respondStatus').value = data.status;
    document.getElementById('respondMessage').value = data.response_message || '';
    respondModal.show();
  }

  function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, "&amp;")
               .replace(/</g, "&lt;")
               .replace(/>/g, "&gt;")
               .replace(/"/g, "&quot;")
               .replace(/'/g, "&#039;");
  }
</script>
