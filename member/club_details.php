<?php
session_start();
include "../includes/database.php";
include "../includes/functions.php";
include "../includes/header.php";

if (!isset($_GET['id'])) {
    die("Club not found.");
}

$club_id = intval($_GET['id']);

$member_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

$success = ""; // success message holder

// =====================
// FETCH CLUB DETAILS
// =====================
$clubSql = "SELECT * FROM clubs WHERE id=?";
$stmt = $connection->prepare($clubSql);
$stmt->bind_param("i", $club_id);
$stmt->execute();
$club = $stmt->get_result()->fetch_assoc();

if (!$club) {
    die("Club not found.");
}

// =====================
// MEMBERSHIP STATUS
// =====================
$membership_status = "";
$membership_id = 0;

if ($member_id && $role === "member") {
    $m = $connection->prepare("SELECT id, status FROM club_members WHERE club_id=? AND user_id=?");
    $m->bind_param("ii", $club_id, $member_id);
    $m->execute();
    $res = $m->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $membership_status = $row['status'];
        $membership_id = $row['id'];
    }
}

// =====================
// JOIN CLUB
// =====================
if (isset($_POST['join_club']) && $member_id && $role === "member") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $join = $connection->prepare("INSERT INTO club_members (club_id, user_id, status, joined_at) VALUES (?, ?, 'pending', NOW())");
    $join->bind_param("ii", $club_id, $member_id);
    $join->execute();

    $success = "Membership request sent!";
}

// =====================
// LEAVE CLUB
// =====================
if (isset($_POST['leave_club']) && $membership_status === "approved") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $leave = $connection->prepare("DELETE FROM club_members WHERE id=?");
    $leave->bind_param("i", $membership_id);
    $leave->execute();

    $success = "You left the club.";
    $membership_status = ""; // reset display
}

// =====================
// REGISTER EVENT (Send Request)
// =====================
if (isset($_POST['register_event_id']) && $member_id && $role === "member") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    // Check if user is an approved member of this club
    if ($membership_status !== "approved") {
        die("You must be an approved member of this club to register for events.");
    }
    
    $eid = intval($_POST['register_event_id']);

    // Check for existing registration
    $check = $connection->prepare("SELECT id, status FROM event_registrations WHERE event_id=? AND member_id=?");
    $check->bind_param("ii", $eid, $member_id);
    $check->execute();
    $existing = $check->get_result();

    if ($existing->num_rows == 0) {
        // New registration request with pending status
        $reg = $connection->prepare("INSERT INTO event_registrations (event_id, member_id, status, registered_at) VALUES (?, ?, 'pending', NOW())");
        $reg->bind_param("ii", $eid, $member_id);
        $reg->execute();
        $success = "Event registration request sent! Waiting for approval.";
    } else {
        $row = $existing->fetch_assoc();
        if ($row['status'] === 'pending') {
            $success = "You already have a pending registration request.";
        } elseif ($row['status'] === 'approved') {
            $success = "You are already registered for this event.";
        } else {
            // Resend rejected request
            $resend = $connection->prepare("UPDATE event_registrations SET status='pending', registered_at=NOW() WHERE id=?");
            $resend->bind_param("i", $row['id']);
            $resend->execute();
            $success = "Registration request re-sent!";
        }
    }
}

// =====================
// LEAVE EVENT
// =====================
if (isset($_POST['leave_event_id']) && $member_id && $role === "member") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $eid = intval($_POST['leave_event_id']);

    $del = $connection->prepare("DELETE FROM event_registrations WHERE event_id=? AND member_id=?");
    $del->bind_param("ii", $eid, $member_id);
    $del->execute();
    $success = "You left the event.";
}

// =====================
// EVENT PAGINATION
// =====================
$limit = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$countSql = "SELECT COUNT(*) AS total FROM events WHERE club_id=?";
$cs = $connection->prepare($countSql);
$cs->bind_param("i", $club_id);
$cs->execute();
$totalEvents = $cs->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalEvents / $limit);

// Fetch events
$eventSql = "SELECT * FROM events WHERE club_id=? ORDER BY date ASC LIMIT ?, ?";
$ev = $connection->prepare($eventSql);
$ev->bind_param("iii", $club_id, $offset, $limit);
$ev->execute();
$events = $ev->get_result();

?>

<main>
<div class="container mt-4">

<a href="clubs.php" class="btn btn-ghost mb-4">← Back to Clubs</a>

<?php if (!empty($success)): ?>
<div style="background:#d4edda; color:#155724; padding:12px; border-radius:6px; margin-bottom:20px;" class="mb-4">
    <?= $success ?>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: start;">
  <!-- Club Info Section -->
  <div>
    <?php if ($club['logo']): ?>
        <img src="<?= htmlspecialchars($club['logo']) ?>" alt="Club logo" style="width: 100%; border-radius: 8px; margin-bottom: 20px; object-fit: cover; height: 300px;">
    <?php endif; ?>

    <h2 class="mb-3"><?= htmlspecialchars($club['name']) ?></h2>
    <p class="text-muted mb-4"><strong>Category:</strong> <span class="badge badge-success"><?= htmlspecialchars($club['category']) ?></span></p>
    <div class="card mb-4">
      <p><strong>Description:</strong></p>
      <p class="text-muted"><?= nl2br(htmlspecialchars($club['description'])) ?></p>
      <p class="mb-1"><strong>Created:</strong> <span class="text-muted"><?= $club['created_at'] ?></span></p>
    </div>

    <!-- Membership Section -->
    <div class="card">
      <h3 class="card-header mb-3">Membership</h3>

      <?php if (!$member_id || $role !== "member"): ?>
          <p class="text-muted">Login as a member to join this club.</p>

      <?php else: ?>

          <?php if ($membership_status === "approved"): ?>
              <p class="badge badge-success mb-3">✓ You are a member of this club</p>
              <form method="POST">
                  <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                  <button type="submit" name="leave_club" class="btn" style="background:#dc3545; color:white;">Leave Club</button>
              </form>

          <?php elseif ($membership_status === "pending"): ?>
              <p class="badge badge-warning">⏳ Your membership request is pending approval</p>

          <?php else: ?>
              <form method="POST">
                  <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                  <button type="submit" name="join_club" class="btn btn-primary">Join Club</button>
              </form>
          <?php endif; ?>

      <?php endif; ?>
    </div>
  </div>

  <!-- Events Section -->
  <div>
    <h3 class="mb-3">Club Events</h3>

    <?php if ($events->num_rows === 0): ?>
        <div class="card text-center text-muted">No events yet.</div>

    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 16px;">
          <?php while ($e = $events->fetch_assoc()): ?>
              <div class="card">
                  <?php if (!empty($e['event_image'])): ?>
                      <img src="<?= htmlspecialchars($e['event_image']) ?>" alt="Event" style="width: 100%; height: 160px; object-fit: cover; border-radius: 6px; margin-bottom: 12px;">
                  <?php endif; ?>

                  <h4 class="mb-2"><?= htmlspecialchars($e['title']) ?></h4>
                  <p class="text-muted mb-2"><?= nl2br(htmlspecialchars($e['description'])) ?></p>
                  <p class="mb-1"><strong>Date:</strong> <?= $e['date'] ?> @ <?= $e['event_time'] ?></p>
                  <p class="mb-1"><strong>Venue:</strong> <?= htmlspecialchars($e['venue']) ?></p>
                  <p class="mb-3"><strong>Deadline:</strong> <?= $e['registration_deadline'] ?></p>
                  <p class="text-muted" style="font-size: 12px;">Max: <?= $e['max_participants'] ?> participants</p>

                  <!-- Registration Status -->
                  <?php
                  $event_id = $e['id'];
                  $check = $connection->prepare("SELECT id, status FROM event_registrations WHERE event_id=? AND member_id=?");
                  $check->bind_param("ii", $event_id, $member_id);
                  $check->execute();
                  $reg_result = $check->get_result();
                  $registration_status = "";
                  if ($reg_result->num_rows > 0) {
                      $reg_row = $reg_result->fetch_assoc();
                      $registration_status = $reg_row['status'];
                  }
                  ?>

                  <div class="mt-3" style="display: flex; gap: 8px;">
                    <?php if ($registration_status === "approved"): ?>
                        <p class="badge badge-success" style="margin: 0;">✓ Registered</p>
                        <form method="POST" style="margin-left: auto;">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="leave_event_id" value="<?= $event_id ?>">
                            <button class="btn" style="background:#dc3545; color:white; padding: 6px 12px;">Leave</button>
                        </form>
                    <?php elseif ($registration_status === "pending"): ?>
                        <p class="badge badge-warning" style="margin: 0;">⏳ Pending</p>
                    <?php elseif ($registration_status === "rejected"): ?>
                        <p class="badge badge-danger" style="margin: 0;">Rejected</p>
                        <form method="POST" style="margin-left: auto;">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="register_event_id" value="<?= $event_id ?>">
                            <button class="btn btn-primary" style="padding: 6px 12px;">Request Again</button>
                        </form>
                    <?php elseif ($member_id && $role === "member" && $membership_status === "approved"): ?>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="register_event_id" value="<?= $event_id ?>">
                            <button class="btn btn-primary">Request Registration</button>
                        </form>
                    <?php elseif (!$member_id || $role !== "member"): ?>
                        <p class="text-muted" style="margin: 0;">Login to register</p>
                    <?php else: ?>
                        <p class="text-muted" style="margin: 0;">Must be approved member</p>
                    <?php endif; ?>
                  </div>
              </div>
          <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-between items-center">
          <?php if ($page > 1): ?>
              <a href="?id=<?= $club_id ?>&page=<?= $page - 1 ?>" class="btn btn-outline">← Previous</a>
          <?php endif; ?>

          <?php if ($page < $totalPages): ?>
              <a href="?id=<?= $club_id ?>&page=<?= $page + 1 ?>" class="btn btn-outline" style="margin-left: auto;">Next →</a>
          <?php endif; ?>
        </div>

    <?php endif; ?>
  </div>
</div>

</div>
</main>

<?php include '../includes/footer.php'; ?>
