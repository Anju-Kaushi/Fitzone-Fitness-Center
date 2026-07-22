<?php
include 'db.php';
session_start();

// Fetch all schedule entries joined with trainers to get trainer names
$sql = "SELECT cs.class_name, cs.trainer_id, cs.time, cs.day, t.name AS trainer_name
        FROM class_schedule cs
        LEFT JOIN trainers t ON cs.trainer_id = t.id
        ORDER BY FIELD(cs.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), cs.time";

$result = $conn->query($sql);

$schedules = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $time = date("g:i A", strtotime($row['time']));
        $day = $row['day'];
        if (!isset($schedules[$time])) {
            $schedules[$time] = [];
        }
        $schedules[$time][$day] = [
            'class_name' => $row['class_name'],
            'trainer_id' => $row['trainer_id'],
            'trainer_name' => $row['trainer_name'], // For display
        ];
    }
}

// Unique times for sorting
$times = array_keys($schedules);
sort($times);

// Fetch all trainers for the filter dropdown (id and name)
$trainersRes = $conn->query("SELECT id, name FROM trainers ORDER BY name");
$trainers = [];
if ($trainersRes) {
    while ($t = $trainersRes->fetch_assoc()) {
        $trainers[] = $t;
    }
}

// Get unique class types for the filter dropdown
$types = array_unique(array_map(fn($timeSlots) => $timeSlots['class_name'] ?? '', array_merge(...array_values($schedules))));
$types = array_filter($types);
sort($types);

// Days order for table header
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Classes | FitZone Fitness Center </title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    /* Custom scrollbar for filter container */
    .filters-container {
      overflow-x: auto;
      white-space: nowrap;
      padding-bottom: 0.5rem;
      scrollbar-width: thin;
      scrollbar-color: #198754 transparent;
    }
    .filters-container::-webkit-scrollbar {
      height: 6px;
    }
    .filters-container::-webkit-scrollbar-thumb {
      background-color: #198754; /* bootstrap green */
      border-radius: 10px;
    }
    /* Sticky first column and header */
    th, td {
      vertical-align: middle !important;
    }
    thead th {
      position: sticky;
      top: 0;
      background: #198754;
      color: white;
      z-index: 10;
    }
    tbody td:first-child,
    thead th:first-child {
      position: sticky;
      left: 0;
      background: white;
      z-index: 20;
      border-right: 2px solid #198754;
      font-weight: 600;
      color: #198754;
    }
    /* Color codes for class types */
    .class-yoga {
      background-color: #fff3cd; /* yellow-100 */
      color: #856404; /* yellow-900 */
      border-radius: 0.375rem;
    }
    .class-cardio {
      background-color: #cfe2ff; /* blue-100 */
      color: #084298; /* blue-900 */
      border-radius: 0.375rem;
    }
    .class-strength {
      background-color: #f8d7da; /* rose-100 */
      color: #842029; /* rose-900 */
      border-radius: 0.375rem;
    }
    .class-default {
      background-color: #e2e3e5; /* gray-100 */
      color: #41464b; /* gray-800 */
      border-radius: 0.375rem;
    }
    /* Hover for table rows */
    tbody tr:hover {
      background-color: #d1e7dd !important; /* green-100 */
      transition: background-color 0.3s;
    }
  </style>
</head>

<body class="bg-light text-dark">

<?php include 'header.php'; ?>

<main class="container py-5">

  <h1 class="display-4 text-center text-success mb-4">Our Class Schedule</h1>
  <p class="lead text-center text-secondary mb-5">
    Find the perfect class for your fitness goals. Use filters below to narrow down your options.
  </p>

  <!--Filters -->
  <div class="filters-container d-flex flex-wrap gap-2 mb-4 px-2 justify-content-center align-items-end">

    <div style="min-width: 120px;">
      <select id="typeFilter" class="form-select form-select-sm" aria-label="Class Type Filter">
        <option value="all" selected>All Types</option>
        <?php foreach ($types as $type): ?>
          <option><?= htmlspecialchars($type) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="min-width: 110px;">
      <select id="timeFilter" class="form-select form-select-sm" aria-label="Time Filter">
        <option value="all" selected>All Times</option>
        <?php foreach ($times as $timeOption): ?>
          <option><?= htmlspecialchars($timeOption) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="min-width: 140px;">
      <select id="instructorFilter" class="form-select form-select-sm" aria-label="Instructor Filter">
        <option value="all" selected>All Instructors</option>
        <?php foreach ($trainers as $trainer): ?>
          <option value="<?= htmlspecialchars((string)$trainer['id']) ?>"><?= htmlspecialchars($trainer['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <button id="viewScheduleBtn" class="btn btn-sm btn-success px-3">
      Reset
    </button>
  </div>

  <!-- Schedule Table -->
  <div class="table-responsive border rounded shadow bg-white">
    <table class="table table-bordered table-hover text-center mb-0 align-middle">
      <thead>
        <tr>
          <th scope="col" style="min-width: 100px;">Time</th>
          <?php foreach ($days as $day): ?>
            <th scope="col"><?= $day ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($times as $index => $time): ?>
          <tr class="<?= $index % 2 === 0 ? '' : 'table-light' ?>">
            <td class="fw-semibold text-success" style="min-width: 100px; white-space: nowrap;"><?= $time ?></td>
            <?php foreach ($days as $day):
              $cell = $schedules[$time][$day] ?? null;
              if ($cell):
                $type = htmlspecialchars($cell['class_name']);
                $trainerId = htmlspecialchars((string)$cell['trainer_id']);
                $trainerName = htmlspecialchars($cell['trainer_name']);
                $typeClass = strtolower($type);
                $colorClass = match ($typeClass) {
                  'yoga' => 'class-yoga',
                  'cardio' => 'class-cardio',
                  'strength' => 'class-strength',
                  default => 'class-default',
                };
              ?>
                <td
                  class="<?= $colorClass ?> cursor-pointer"
                  data-type="<?= $type ?>"
                  data-time="<?= $time ?>"
                  data-instructor="<?= $trainerId ?>"
                  title="<?= "$type with $trainerName" ?>"
                  style="user-select: none;"
                >
                  <div class="fw-bold small mb-1"><?= $type ?></div>
                  <div class="small text-truncate"><?= $trainerName ?></div>
                </td>
              <?php else: ?>
                <td class="text-muted">—</td>
              <?php endif; endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Book a Class Button -->
  <div class="text-center mt-5">
    <?php if (!isset($_SESSION['user'])): ?>
      <a href="login.php" class="btn btn-success btn-lg px-5 shadow">
        Book a Class
      </a>
    <?php else: ?>
      <a href="book-class.php" class="btn btn-success btn-lg px-5 shadow">
        Book a Class
      </a>
    <?php endif; ?>
  </div>

</main>

<!-- Bootstrap 5 JS bundle (Popper + JS) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const typeFilter = document.getElementById("typeFilter");
  const timeFilter = document.getElementById("timeFilter");
  const instructorFilter = document.getElementById("instructorFilter");
  const viewScheduleBtn = document.getElementById("viewScheduleBtn");

  function filterClasses() {
    const type = typeFilter.value.toLowerCase();
    const time = timeFilter.value;
    const instructor = instructorFilter.value;

    document.querySelectorAll("tbody tr").forEach(row => {
      const classCells = Array.from(row.querySelectorAll("td[data-type]"));

      let anyCellMatches = false;

      classCells.forEach(cell => {
        const cellType = cell.dataset.type.toLowerCase();
        const cellTime = cell.dataset.time;
        const cellInstructor = String(cell.dataset.instructor);

        const matchType = (type === "all" || cellType === type);
        const matchTime = (time === "all" || cellTime === time);
        const matchInstructor = (instructor === "all" || cellInstructor === instructor);

        const shouldShow = matchType && matchTime && matchInstructor;
        cell.style.display = shouldShow ? "" : "none";

        if (shouldShow) anyCellMatches = true;
      });

      // Show/hide entire row based on matching cells
      row.style.display = anyCellMatches ? "" : "none";
    });
  }

  typeFilter.addEventListener("change", filterClasses);
  timeFilter.addEventListener("change", filterClasses);
  instructorFilter.addEventListener("change", filterClasses);

  viewScheduleBtn.addEventListener("click", () => {
    typeFilter.value = "all";
    timeFilter.value = "all";
    instructorFilter.value = "all";
    filterClasses();
  });

  // Initialize filter on page load
  filterClasses();
</script>

<?php include 'footer.php'; ?>

</body>
</html>
