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
$success = "";

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
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $leave = $connection->prepare("DELETE FROM club_members WHERE id=?");
    $leave->bind_param("i", $membership_id);
    $leave->execute();

    $success = "You left the club.";
    $membership_status = "";
}

// =====================
// REGISTER EVENT
// =====================
if (isset($_POST['register_event_id']) && $member_id && $role === "member") {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    if ($membership_status !== "approved") {
        die("You must be an approved member of this club to register for events.");
    }
    
    $eid = intval($_POST['register_event_id']);

    $check = $connection->prepare("SELECT id, status FROM event_registrations WHERE event_id=? AND member_id=?");
    $check->bind_param("ii", $eid, $member_id);
    $check->execute();
    $existing = $check->get_result();

    if ($existing->num_rows == 0) {
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

$eventSql = "SELECT * FROM events WHERE club_id=? ORDER BY date ASC LIMIT ?, ?";
$ev = $connection->prepare($eventSql);
$ev->bind_param("iii", $club_id, $offset, $limit);
$ev->execute();
$events = $ev->get_result();
?>

<main>
<div class="container mt-4">

<a href="view_clubs.php" class="btn btn-ghost mb-4">← Back to Clubs</a>

<?php if (!empty($success)): ?>
<div class="alert-success mb-4">
    <?= $success ?>
</div>
<?php endif; ?>

<div class="club-layout">

    <!-- LEFT: Club Information -->
    <div class="club-info">

        <!-- Club Logo with fallback -->
        <img src="<?= !empty($club['logo']) ? htmlspecialchars($club['logo']) : '../includes/images/default_img.jpeg' ?>" 
             alt="Club Logo" 
             class="club-banner"
             onerror="this.onerror=null;this.src='../includes/images/default_img.jpeg';">

        <h2 class="club-title"><?= htmlspecialchars($club['name']) ?></h2>

        <p class="text-muted mb-2">
            <strong>Category:</strong> 
            <span class="badge badge-success"><?= htmlspecialchars($club['category']) ?></span>
        </p>

        <div class="card mb-4">
            <p><strong>Description:</strong></p>
            <p class="text-muted"><?= nl2br(htmlspecialchars($club['description'])) ?></p>
            <p class="mt-3"><strong>Created:</strong> <span class="text-muted"><?= $club['created_at'] ?></span></p>
        </div>

        <!-- Membership Box -->
        <div class="card membership-box">
            <h3 class="card-header mb-3">Membership</h3>

            <?php if (!$member_id || $role !== "member"): ?>
                <p class="text-muted">Login as a member to join this club.</p>
            <?php else: ?>
                <?php if ($membership_status === "approved"): ?>
                    <p class="badge badge-success mb-3">✓ You are a member</p>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <button type="submit" name="leave_club" class="btn btn-danger">Leave Club</button>
                    </form>
                <?php elseif ($membership_status === "pending"): ?>
                    <p class="badge badge-warning">⏳ Request pending</p>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <button type="submit" name="join_club" class="btn btn-primary">Join Club</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- RIGHT: Events Section -->
<div class="club-events">

    <h3 class="section-title">Club Events</h3>

    <?php if ($events->num_rows === 0): ?>
        <div class="card text-center text-muted">No events yet.</div>
    <?php else: ?>
        <div class="grid-cards">
            <?php while ($e = $events->fetch_assoc()): ?>

                <div class="card">

                    <!-- Event Image -->
                   <img src="<?= !empty($e['event_image']) ? $e['event_image'] : '..\includes\images\default_img.jpeg' ?>"
                         class="card-img-top"
                         alt="Event Image">

                    <h4 class="card-header"><?= htmlspecialchars($e['title']) ?></h4>

                    <p class="text-muted mb-2 small">
                        <strong>Date:</strong> <?= $e['date'] ?> @ <?= $e['event_time'] ?><br>
                        <strong>Venue:</strong> <?= htmlspecialchars($e['venue']) ?><br>
                        <strong>Deadline:</strong> <?= htmlspecialchars($e['registration_deadline']) ?>
                    </p>

                    <p class="text-muted"><?= nl2br(htmlspecialchars(substr($e['description'], 0, 120))) ?>...</p>

                    <!-- Registration Logic -->
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

                    <div class="mt-2">
                        <?php if ($registration_status === "approved"): ?>
                            <span class="badge badge-success">✓ Registered</span>
                        <?php elseif ($registration_status === "pending"): ?>
                            <span class="badge badge-warning">⏳ Pending</span>
                        <?php elseif ($registration_status === "rejected"): ?>
                            <span class="badge badge-danger">Rejected</span>
                        <?php elseif ($member_id && $role === "member" && $membership_status === "approved"): ?>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                                <input type="hidden" name="register_event_id" value="<?= $event_id ?>">
                                <button class="btn btn-primary w-full">Request Registration</button>
                            </form>
                        <?php else: ?>
                            <span class="text-muted">Login or become approved member</span>
                        <?php endif; ?>
                    </div>

                </div>

            <?php endwhile; ?>
        </div>
    <?php endif; ?>

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

</div>

</div>
</div>
</main>

<?php include '../includes/footer.php'; ?>
