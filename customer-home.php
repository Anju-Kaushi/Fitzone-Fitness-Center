<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

$email = $_SESSION['user'] ?? '';

if (!$email) {
    echo "You must be logged in to view this page.";
    exit;
}

// Get user ID and full name from email
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

// Fetch approved membership (no start/end dates in table)
$stmt = $conn->prepare("SELECT um.*, mp.name AS plan_name, mp.features, mp.monthly_price, mp.quarterly_price, mp.yearly_price 
                        FROM user_memberships um
                        JOIN membership_plans mp ON um.plan_id = mp.id
                        WHERE um.user_id = ? AND um.status = 'Approved' LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Customer Home | Fitzone Fitness Center</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

<div class="container my-5">
    <h1 class="text-success fw-bold mb-4">Welcome, <?= htmlspecialchars($fullname) ?> 👋</h1>

    <?php 
    if ($result->num_rows > 0):
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

        // Parse features string into associative array safely
        $featuresArr = [];
        $featuresStr = $membership['features'];
        $featuresParts = explode(',', $featuresStr);

        foreach ($featuresParts as $pair) {
            $pair = trim($pair);
            $kv = explode(':', $pair);

            if (count($kv) === 2) {
                $key = trim($kv[0]);
                $value = trim($kv[1]);
                $featuresArr[$key] = $value;
            }
        }
    ?>
        <div class="bg-success bg-opacity-10 border border-success rounded p-4 shadow-sm">
            <h2 class="h5 text-success fw-semibold">Your Current Membership</h2>
            <p class="mt-2"><strong>Plan:</strong> <?= htmlspecialchars($membership['plan_name']) ?></p>
            <p><strong>Plan Type:</strong> <?= htmlspecialchars(ucfirst($membership['plan_type'])) ?></p>
            <!-- Start and End Dates not available, so removed -->

            <div class="mt-2">
                <strong>Features:</strong>
                <p class="text-muted" style="white-space: pre-line;"><?= htmlspecialchars($membership['features']) ?></p>
            </div>
            <div class="mt-2 text-muted">
                <p><strong><?= $priceLabel ?> Price:</strong> Rs. <?= number_format($price, 2) ?></p>
            </div>
        </div>

    <?php else:
        // No approved membership - show all active plans
        $plans = mysqli_query($conn, "SELECT * FROM membership_plans WHERE status = 'Active'");
    ?>
        <h2 class="h5 fw-semibold text-secondary mb-3">You don’t have a membership yet</h2>
        <p class="mb-4 text-muted">Choose a plan below to get started:</p>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            <?php while ($plan = mysqli_fetch_assoc($plans)): ?>
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
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
