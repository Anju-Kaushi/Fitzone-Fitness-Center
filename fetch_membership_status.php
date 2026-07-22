<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

$email = $_SESSION['user'] ?? '';

if (!$email) {
    echo "You must be logged in.";
    exit;
}

// Get user ID
$stmtUser = $conn->prepare("SELECT id, fullname FROM tbluser WHERE email = ?");
$stmtUser->bind_param("s", $email);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

if ($resultUser->num_rows === 0) {
    echo "User not found.";
    exit;
}

$user = $resultUser->fetch_assoc();
$user_id = $user['id'];
$fullname = $user['fullname'];

// Fetch active membership
$stmt = $conn->prepare("SELECT um.*, mp.name AS plan_name, mp.features, mp.monthly_price, mp.quarterly_price, mp.yearly_price 
                        FROM user_memberships um
                        JOIN membership_plans mp ON um.plan_id = mp.id
                        WHERE um.user_id = ? AND um.status = 'Active' LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $membership = $result->fetch_assoc();
    $planType = strtolower($membership['plan_type']);
    $price = 0;
    $priceLabel = ucfirst($planType);

    if ($planType === 'monthly') {
        $price = $membership['monthly_price'];
    } elseif ($planType === 'quarterly') {
        $price = $membership['quarterly_price'];
    } elseif ($planType === 'yearly') {
        $price = $membership['yearly_price'];
    }
    ?>

    <h1 class="text-success fw-bold mb-4">Welcome, <?= htmlspecialchars($fullname) ?> 👋</h1>

    <div class="bg-success bg-opacity-10 border border-success rounded p-4 shadow-sm" id="active-membership">
        <h2 class="h5 text-success fw-semibold">Your Current Membership</h2>
        <p class="mt-2"><strong>Plan:</strong> <?= htmlspecialchars($membership['plan_name']) ?></p>
        <p><strong>Plan Type:</strong> <?= htmlspecialchars(ucfirst($membership['plan_type'])) ?></p>
        <p><strong>Start Date:</strong> <?= htmlspecialchars($membership['start_date']) ?></p>
        <p><strong>End Date:</strong> <?= htmlspecialchars($membership['end_date']) ?></p>
        <div class="mt-2">
            <strong>Features:</strong>
            <p class="text-muted" style="white-space: pre-line;"><?= htmlspecialchars($membership['features']) ?></p>
        </div>
        <div class="mt-2 text-muted">
            <p><strong><?= $priceLabel ?> Price:</strong> Rs. <?= number_format($price, 2) ?></p>
        </div>
    </div>

    <?php
} else {
    // No active membership
    $plansResult = mysqli_query($conn, "SELECT * FROM membership_plans WHERE status = 'Active'");
    $plans = [];
    while ($plan = mysqli_fetch_assoc($plansResult)) {
        $plans[] = $plan;
    }
    ?>

    <h1 class="text-success fw-bold mb-4">Welcome, <?= htmlspecialchars($fullname) ?> 👋</h1>

    <h2 class="h5 fw-semibold text-secondary mb-3">You don’t have a membership yet</h2>
    <p class="mb-4 text-muted">Choose a plan below to get started:</p>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4" id="available-plans">
        <?php foreach ($plans as $plan): ?>
            <div class="col">
                <div class="card h-100 border shadow-sm">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h6 text-success"><?= htmlspecialchars($plan['name']) ?></h3>
                            <p class="text-muted small" style="white-space: pre-line;"><?= htmlspecialchars($plan['features']) ?></p>
                        </div>
                        <div class="mt-3 text-muted small">
                            <p class="mb-1"><strong>Monthly:</strong> Rs. <?= number_format($plan['monthly_price'], 2) ?></p>
                            <p class="mb-1"><strong>Quarterly:</strong> Rs. <?= number_format($plan['quarterly_price'], 2) ?></p>
                            <p class="mb-1"><strong>Yearly:</strong> Rs. <?= number_format($plan['yearly_price'], 2) ?></p>
                        </div>
                        <a href="subscribe.php?plan_id=<?= $plan['id'] ?>" class="btn btn-success mt-3">
                            Choose Plan
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php
}
?>
