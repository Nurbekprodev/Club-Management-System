<?php
session_start();
include "../includes/database.php";
include "../includes/functions.php";

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
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($club['name']) ?> - Club Details</title>
</head>
<body>

<a href="clubs.php">← Back to Clubs</a>

<br><br>

<!-- SUCCESS MESSAGE -->
<?php if (!empty($success)): ?>
<div style="background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-bottom:15px;">
    <?= $success ?>
</div>
<?php endif; ?>

<h2><?= htmlspecialchars($club['name']) ?></h2>

<?php if ($club['logo']): ?>
    <img src="<?= htmlspecialchars($club['logo']) ?>" width="120"><br><br>
<?php endif; ?>

<p><b>Category:</b> <?= htmlspecialchars($club['category']) ?></p>
<p><b>Description:</b> <?= nl2br(htmlspecialchars($club['description'])) ?></p>
<p><b>Created At:</b> <?= $club['created_at'] ?></p>

<hr>

<!-- MEMBERSHIP SECTION -->
<h3>Membership</h3>

<?php if (!$member_id || $role !== "member"): ?>
    <p>Login as a member to join this club.</p>

<?php else: ?>

    <?php if ($membership_status === "approved"): ?>
        <p style="color:green"><b>You are a member of this club.</b></p>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <button type="submit" name="leave_club" style="background:red; color:white">Leave Club</button>
        </form>

    <?php elseif ($membership_status === "pending"): ?>
        <p style="color:orange"><b>Your membership request is pending approval.</b></p>

    <?php else: ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <button type="submit" name="join_club" style="background:green; color:white">Join Club</button>
        </form>
    <?php endif; ?>

<?php endif; ?>

<hr>

<!-- EVENTS SECTION -->
<h3>Club Events</h3>

<?php if ($events->num_rows === 0): ?>
    <p>No events yet.</p>

<?php else: ?>
    <?php while ($e = $events->fetch_assoc()): ?>
        <div style="border:1px solid #aaa; padding:12px; margin-bottom:12px;">

            <h4><?= htmlspecialchars($e['title']) ?></h4>

            <?php if (!empty($e['event_image'])): ?>
                <img src="<?= htmlspecialchars($e['event_image']) ?>" width="150"><br><br>
            <?php endif; ?>

            <p><?= nl2br(htmlspecialchars($e['description'])) ?></p>
            <p><b>Date:</b> <?= $e['date'] ?> (<?= $e['event_time'] ?>)</p>
            <p><b>Venue:</b> <?= htmlspecialchars($e['venue']) ?></p>
            <p><b>Registration Deadline:</b> <?= $e['registration_deadline'] ?></p>
            <p><b>Max Participants:</b> <?= $e['max_participants'] ?></p>

            <!-- CHECK REGISTRATION STATUS -->
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

            <!-- REGISTRATION STATUS & BUTTONS -->
            <?php if ($registration_status === "approved"): ?>
                <p style="color:green;"><b>You are registered for this event!</b></p>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="leave_event_id" value="<?= $event_id ?>">
                    <button style="background:red; color:white; padding:6px 12px; border:none;">Leave Event</button>
                </form>
            <?php elseif ($registration_status === "pending"): ?>
                <p style="color:orange;"><b>Registration request pending approval.</b></p>
            <?php elseif ($registration_status === "rejected"): ?>
                <p style="color:red;"><b>Your registration was rejected.</b></p>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="register_event_id" value="<?= $event_id ?>">
                    <button style="background:green; color:white; padding:6px 12px; border:none;">Request Again</button>
                </form>
            <?php elseif ($member_id && $role === "member" && $membership_status === "approved"): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="register_event_id" value="<?= $event_id ?>">
                    <button style="background:green; color:white; padding:6px 12px; border:none;">Request Registration</button>
                </form>
            <?php elseif (!$member_id || $role !== "member"): ?>
                <p style="color:orange;">Login to register for events.</p>
            <?php else: ?>
                <p style="color:orange;">You must be an approved member to register for events.</p>
            <?php endif; ?>

        </div>
    <?php endwhile; ?>

    <!-- PAGINATION -->
    <div style="margin-top:20px;">
        <?php if ($page > 1): ?>
            <a href="?id=<?= $club_id ?>&page=<?= $page - 1 ?>">← Previous</a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?id=<?= $club_id ?>&page=<?= $page + 1 ?>" style="margin-left:20px;">Next →</a>
        <?php endif; ?>
    </div>

<?php endif; ?>

</body>
</html>
