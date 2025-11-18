<?php
session_start();
include "../includes/database.php";
include "../includes/functions.php";
include "../includes/header.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: ../public/login.php");
    exit();
}

$member_id = $_SESSION['user_id'];


// Handle register event
if (isset($_POST['register_event_id']) && $member_id && $_SESSION['role'] === 'member') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $event_id = intval($_POST['register_event_id']);
    $ev = $connection->prepare("SELECT club_id FROM events WHERE id=?");
    $ev->bind_param("i", $event_id);
    $ev->execute();
    $evR = $ev->get_result();
    if ($evR->num_rows === 0) {
        die("Event not found.");
    }
    $eventRow = $evR->fetch_assoc();
    $clubIdForEvent = $eventRow['club_id'];

    $m = $connection->prepare("SELECT status FROM club_members WHERE club_id=? AND user_id=?");
    $m->bind_param("ii", $clubIdForEvent, $member_id);
    $m->execute();
    $mres = $m->get_result();
    $membership_status = '';
    if ($mres->num_rows > 0) {
        $membership_status = $mres->fetch_assoc()['status'];
    }

    if ($membership_status !== 'approved') {
        die("You must be an approved member of this club to register for events.");
    }

    $check = $connection->prepare("SELECT id, status FROM event_registrations WHERE event_id=? AND member_id=?");
    $check->bind_param("ii", $event_id, $member_id);
    $check->execute();
    $existing = $check->get_result();

    if ($existing->num_rows == 0) {
        $reg = $connection->prepare("INSERT INTO event_registrations (event_id, member_id, status, registered_at) VALUES (?, ?, 'pending', NOW())");
        $reg->bind_param("ii", $event_id, $member_id);
        $reg->execute();
    } else {
        $row = $existing->fetch_assoc();
        if ($row['status'] !== 'pending' && $row['status'] !== 'approved') {
            $resend = $connection->prepare("UPDATE event_registrations SET status='pending', registered_at=NOW() WHERE id=?");
            $resend->bind_param("i", $row['id']);
            $resend->execute();
        }
    }

    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?') . "?" . $_SERVER['QUERY_STRING']);
    exit();
}


// Handle leave event
if (isset($_POST['leave_event_id']) && $member_id && $_SESSION['role'] === 'member') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $event_id = intval($_POST['leave_event_id']);
    $delete = $connection->prepare("DELETE FROM event_registrations WHERE event_id=? AND member_id=?");
    $delete->bind_param("ii", $event_id, $member_id);
    $delete->execute();

    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?') . "?" . $_SERVER['QUERY_STRING']);
    exit();
}


// Filter inputs
$q             = $_GET['q'] ?? '';
$filter_club   = !empty($_GET['club_id']) ? intval($_GET['club_id']) : null;
$section       = $_GET['section'] ?? 'upcoming';
$page          = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit         = 10;
$offset        = ($page - 1) * $limit;


// Build WHERE clause
$where  = " WHERE 1=1 ";
$types  = "";
$params = [];

if ($q !== "") {
    $where .= " AND (e.title LIKE ? OR c.name LIKE ?) ";
    $types .= "ss";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if ($filter_club !== null) {
    $where .= " AND e.club_id=? ";
    $types .= "i";
    $params[] = $filter_club;
}

if ($section === "upcoming") {
    $where .= " AND e.date >= CURDATE() ";
} elseif ($section === "past") {
    $where .= " AND e.date < CURDATE() ";
}


// Count for pagination
$count_sql = "
    SELECT COUNT(*) AS total
    FROM events e
    JOIN clubs c ON e.club_id = c.id
    $where
";

$stmt = $connection->prepare($count_sql);
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = max(1, ceil($total / $limit));
$stmt->close();


// Fetch events
$sql = "
    SELECT e.id AS event_id, e.*, c.name AS club_name, c.id AS club_id
    FROM events e
    JOIN clubs c ON e.club_id = c.id
    $where
    ORDER BY e.date ASC, e.event_time ASC
    LIMIT ? OFFSET ?
";

$types2   = $types . "ii";
$params2  = array_merge($params, [$limit, $offset]);

$stmt = $connection->prepare($sql);
$stmt->bind_param($types2, ...$params2);
$stmt->execute();
$events_result = $stmt->get_result();

?>

<main>
<div class="container mt-4">

<div class="d-flex justify-between items-center mb-4">
  <h2>Browse Events</h2>
  <a href="dashboard.php" class="btn btn-ghost">??Back to Dashboard</a>
</div>


<!-- Search & Filter Form -->
<div class="card mb-4">
  <form method="GET" action="" class="" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 12px; align-items: end;">
    <div class="form-group" style="margin: 0;">
      <label>Search:</label>
      <input type="text" name="q" class="form-control" placeholder="Title or club" value="<?= htmlspecialchars($q) ?>">
    </div>

    <div class="form-group" style="margin: 0;">
      <label>Club:</label>
      <select name="club_id" class="form-control">
        <option value="">All Clubs</option>
        <?php
        $cq = mysqli_query($connection, "SELECT id, name FROM clubs ORDER BY name ASC");
        while ($c = mysqli_fetch_assoc($cq)):
            $sel = ($filter_club == $c['id']) ? "selected" : "";
        ?>
            <option value="<?= $c['id'] ?>" <?= $sel ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="form-group" style="margin: 0;">
      <label>When:</label>
      <select name="section" class="form-control">
        <option value="upcoming" <?= $section === "upcoming" ? "selected" : "" ?>>Upcoming</option>
        <option value="past" <?= $section === "past" ? "selected" : "" ?>>Past</option>
        <option value="all" <?= $section === "all" ? "selected" : "" ?>>All</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Search</button>
  </form>
</div>


<!-- Events List -->
<?php if ($events_result->num_rows === 0): ?>

    <div class="card text-center text-muted">
      <p>No events found matching your criteria.</p>
    </div>

<?php else: ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-bottom: 30px;">
      <?php while ($e = $events_result->fetch_assoc()): ?>
          <div class="card">

              <?php if (!empty($e['event_image'])): ?>
                  <img src="<?= htmlspecialchars($e['event_image']) ?>" alt="Event" class="card-img-top" style="height: 200px; object-fit: cover;">
              <?php endif; ?>

              <h4 class="card-header mb-2" style="margin-top: 8px;"><?= htmlspecialchars($e['title']) ?></h4>
              
              <p class="text-muted mb-2" style="font-size: 12px;">
                <strong>Club:</strong>
                <a href="club_details.php?id=<?= $e['club_id'] ?>">
                    <?= htmlspecialchars($e['club_name']) ?>
                </a>
              </p>

              <p class="text-muted mb-2" style="font-size: 13px;"><?= nl2br(htmlspecialchars(substr($e['description'], 0, 100))) ?>...</p>

              <p class="mb-2" style="font-size: 12px;">
                <strong>?��</strong> <?= $e['date'] ?> @ <?= $e['event_time'] ?><br>
                <strong>?��</strong> <?= htmlspecialchars($e['venue']) ?><br>
                <strong>??/strong> Deadline: <?= htmlspecialchars($e['registration_deadline']) ?>
              </p>


              <!-- Register Button -->
              <?php
              $event_id = $e['event_id'];
              $check = $connection->prepare("SELECT id, status FROM event_registrations WHERE event_id=? AND member_id=?");
              $check->bind_param("ii", $event_id, $member_id);
              $check->execute();
              $reg_result = $check->get_result();
              $registration_status = "";
              if ($reg_result->num_rows > 0) {
                  $reg_row = $reg_result->fetch_assoc();
                  $registration_status = $reg_row['status'];
              }

              $membership_status = '';
              if ($member_id && isset($e['club_id'])) {
                  $m = $connection->prepare("SELECT status FROM club_members WHERE club_id=? AND user_id=?");
                  $m->bind_param("ii", $e['club_id'], $member_id);
                  $m->execute();
                  $mres = $m->get_result();
                  if ($mres->num_rows > 0) {
                      $membership_status = $mres->fetch_assoc()['status'];
                  }
              }
              ?>

              <div class="mt-3">
                <?php if ($registration_status === "approved"): ?>
                    <p class="badge badge-success" style="margin: 0; padding: 6px 10px;">??Registered</p>
                    <form method="POST" style="margin-top: 8px;">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="leave_event_id" value="<?= $event_id ?>">
                        <button class="btn" style="background: #dc3545; color: white; width: 100%; padding: 8px;">Leave Event</button>
                    </form>

                <?php elseif ($registration_status === "pending"): ?>
                    <p class="badge badge-warning" style="margin: 0; padding: 6px 10px;">??Pending</p>

                <?php elseif ($registration_status === "rejected"): ?>
                    <p class="badge badge-danger" style="margin: 0; padding: 6px 10px;">??Rejected</p>
                    <form method="POST" style="margin-top: 8px;">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="register_event_id" value="<?= $event_id ?>">
                        <button class="btn btn-primary" style="width: 100%; padding: 8px;">Request Again</button>
                    </form>

                <?php elseif ($member_id && $_SESSION['role'] === 'member' && $membership_status === 'approved'): ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="register_event_id" value="<?= $event_id ?>">
                        <button class="btn btn-primary" style="width: 100%; padding: 8px;">Request Registration</button>
                    </form>

                <?php elseif (!$member_id || $_SESSION['role'] !== 'member'): ?>
                    <p class="text-muted" style="margin: 0; font-size: 12px;">Login to register</p>

                <?php else: ?>
                    <p class="text-muted" style="margin: 0; font-size: 12px;">Join club first</p>
                <?php endif; ?>
              </div>
          </div>
      <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-between items-center mt-4" style="padding-top: 20px; border-top: 1px solid var(--border-color);">
      <p class="text-muted">Page <?= $page ?> of <?= $totalPages ?></p>

      <div style="display: flex; gap: 8px;">
        <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$page-1])) ?>" class="btn btn-outline">??Previous</a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$page+1])) ?>" class="btn btn-outline">Next ??/a>
        <?php endif; ?>
      </div>
    </div>

<?php endif; ?>

</div>
</main>

<?php include '../includes/footer.php'; ?>
