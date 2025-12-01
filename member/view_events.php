<?php
session_start();
include "../includes/database.php";
include "../includes/functions.php";
include "../includes/header.php";

$filter = $_GET['filter'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0; // fixed from member_id
$member_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

/* ---------------------------------------------------
   FILTERS & PAGINATION
---------------------------------------------------- */
$q           = $_GET['q'] ?? '';
$filter_club = !empty($_GET['club_id']) ? intval($_GET['club_id']) : null;
$section     = $_GET['section'] ?? 'upcoming';
$page        = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

$limit       = 6;
$offset      = ($page - 1) * $limit;

$where  = " WHERE 1=1 ";
$types  = "";
$params = [];

/* Search */
if ($q !== "") {
    $where .= " AND (e.title LIKE ? OR c.name LIKE ?) ";
    $types .= "ss";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

/* Club Filter */
if ($filter_club !== null) {
    $where .= " AND e.club_id=? ";
    $types .= "i";
    $params[] = $filter_club;
}

/* Upcoming / Past / All */
if ($section === "upcoming") {
    $where .= " AND e.date >= CURDATE() ";
} elseif ($section === "past") {
    $where .= " AND e.date < CURDATE() ";
}

/* ----------------------------
   PAGINATION COUNT QUERY
----------------------------- */
$count_sql = "
    SELECT COUNT(*) AS total
    FROM events e
    JOIN clubs c ON e.club_id = c.id
    $where
";

$stmt = $connection->prepare($count_sql);
if ($types !== "") $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = max(1, ceil($total / $limit));
$stmt->close();

/* -------------------------------------
   FETCH EVENTS (NORMAL + JOINED FILTER)
-------------------------------------- */
if ($filter === 'joined' && $user_id) {
    $sql = "
        SELECT e.*, c.name AS club_name
        FROM events e
        JOIN clubs c ON e.club_id = c.id
        JOIN event_registrations r ON r.event_id = e.id
        WHERE r.member_id = ?
        ORDER BY e.date ASC
        LIMIT ? OFFSET ?
    ";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("iii", $user_id, $limit, $offset);
} else {
    $sql = "
        SELECT e.*, c.name AS club_name
        FROM events e
        JOIN clubs c ON e.club_id = c.id
        $where
        ORDER BY e.date ASC
        LIMIT ? OFFSET ?
    ";
    $stmt = $connection->prepare($sql);
    if ($types !== "") {
        $types2  = $types . "ii";
        $params2 = array_merge($params, [$limit, $offset]);
        $stmt->bind_param($types2, ...$params2);
    } else {
        $stmt->bind_param("ii", $limit, $offset);
    }
}

$stmt->execute();
$events = $stmt->get_result();
?>

<main>
<div class="container mt-4">

<div class="d-flex justify-between items-center mb-4">
  <h2>Browse Events</h2>
  <a href="dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
</div>

<!-- Search + Filter Form -->
<div class="card mb-4">
  <form method="GET" action="" class="grid-4">
    
    <div class="form-group">
      <label>Search:</label>
      <input type="text" name="q" class="form-control"
             placeholder="Title or club"
             value="<?= htmlspecialchars($q) ?>">
    </div>

    <div class="form-group">
      <label>Club:</label>
      <select name="club_id" class="form-control">
          <option value="">All Clubs</option>
          <?php
          $cq = mysqli_query($connection, "SELECT id, name FROM clubs ORDER BY name ASC");
          while ($c = mysqli_fetch_assoc($cq)):
              $sel = ($filter_club == $c['id']) ? "selected" : "";
          ?>
              <option value="<?= $c['id'] ?>" <?= $sel ?>>
                <?= htmlspecialchars($c['name']) ?>
              </option>
          <?php endwhile; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Filter:</label>
      <select name="filter" class="form-control">
          <option value="">All Events</option>
          <option value="joined" <?= $filter === 'joined' ? 'selected' : '' ?>>Joined Events</option>
      </select>
    </div>

    <div class="form-group">
      <label>When:</label>
      <select name="section" class="form-control">
        <option value="upcoming" <?= $section === "upcoming" ? "selected" : "" ?>>Upcoming</option>
        <option value="past" <?= $section === "past" ? "selected" : "" ?>>Past</option>
        <option value="all" <?= $section === "all" ? "selected" : "" ?>>All</option>
      </select>
    </div>

    <div class="form-group flex column justify-end">
      <button type="submit" class="btn btn-primary w-full">Search</button>
    </div>

  </form>
</div>

<!-- Event Cards -->
<?php if ($events->num_rows === 0): ?>
  <div class="card text-center text-muted">
    <p>No events found.</p>
  </div>
<?php else: ?>
  <div class="grid-cards">
    <?php while ($e = $events->fetch_assoc()): ?>
      <div class="card">

        <!-- Event Image -->
        <img src="<?= !empty($e['event_image']) ? $e['event_image'] : '../includes/images/default_img.jpeg' ?>"
             class="event-image" 

        <h4 class="card-header"><?= htmlspecialchars($e['title']) ?></h4>

        <p class="text-muted mb-2 small">
          <strong>Club:</strong>
          <a href="club_details.php?id=<?= $e['club_id'] ?>">
            <?= htmlspecialchars($e['club_name']) ?>
          </a>
        </p>

        <p class="text-muted"><?= nl2br(htmlspecialchars(substr($e['description'], 0, 120))) ?>...</p>

        <p class="small">
          📅 <?= $e['date'] ?> @ <?= $e['event_time'] ?><br>
          📍 <?= htmlspecialchars($e['venue']) ?><br>
          ⏳ Deadline: <?= htmlspecialchars($e['registration_deadline']) ?>
        </p>

      </div>
    <?php endwhile; ?>
  </div>

<!-- Pagination -->
<div class="pagination flex gap-2 mt-4 justify-center items-center">

<?php
$adjacents = 2; // Number of pages to show on each side of current
$start = max(1, $page - $adjacents);
$end   = min($totalPages, $page + $adjacents);

// Preserve all GET parameters
$getParams = $_GET;
?>

<?php if ($page > 1): 
    $getParams['page'] = $page - 1; ?>
    <a href="?<?= http_build_query($getParams) ?>" class="btn btn-outline">← Previous</a>
<?php endif; ?>

<?php if ($start > 1): 
    $getParams['page'] = 1; ?>
    <a href="?<?= http_build_query($getParams) ?>" class="btn btn-outline">1</a>
    <?php if ($start > 2): ?><span class="px-2">...</span><?php endif; ?>
<?php endif; ?>

<?php for ($i = $start; $i <= $end; $i++): 
    $getParams['page'] = $i; ?>
    <a href="?<?= http_build_query($getParams) ?>" class="btn <?= ($i == $page) ? 'btn-primary' : 'btn-outline' ?>">
        <?= $i ?>
    </a>
<?php endfor; ?>

<?php if ($end < $totalPages): 
    $getParams['page'] = $totalPages; ?>
    <?php if ($end < $totalPages - 1): ?><span class="px-2">...</span><?php endif; ?>
    <a href="?<?= http_build_query($getParams) ?>" class="btn btn-outline"><?= $totalPages ?></a>
<?php endif; ?>

<?php if ($page < $totalPages): 
    $getParams['page'] = $page + 1; ?>
    <a href="?<?= http_build_query($getParams) ?>" class="btn btn-outline">Next →</a>
<?php endif; ?>

</div>



<?php endif; ?>

</div>
</main>

<?php include '../includes/footer.php'; ?>
