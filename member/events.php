<?php
session_start();
include "../includes/database.php";
include "../includes/functions.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: ../public/login.php");
    exit();
}

$member_id = $_SESSION['user_id'];


// -----------------------------------------------------
//  REGISTER EVENT (send request -> pending)
// -----------------------------------------------------
if (isset($_POST['register_event_id']) && $member_id && $_SESSION['role'] === 'member') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $event_id = intval($_POST['register_event_id']);

    // Find the event and its club
    $ev = $connection->prepare("SELECT club_id FROM events WHERE id=?");
    $ev->bind_param("i", $event_id);
    $ev->execute();
    $evR = $ev->get_result();
    if ($evR->num_rows === 0) {
        die("Event not found.");
    }
    $eventRow = $evR->fetch_assoc();
    $clubIdForEvent = $eventRow['club_id'];

    // Check membership status for that club
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

    // Check for existing registration
    $check = $connection->prepare("SELECT id, status FROM event_registrations WHERE event_id=? AND member_id=?");
    $check->bind_param("ii", $event_id, $member_id);
    $check->execute();
    $existing = $check->get_result();

    if ($existing->num_rows == 0) {
        // New registration request with pending status
        $reg = $connection->prepare("INSERT INTO event_registrations (event_id, member_id, status, registered_at) VALUES (?, ?, 'pending', NOW())");
        $reg->bind_param("ii", $event_id, $member_id);
        $reg->execute();
    } else {
        $row = $existing->fetch_assoc();
        if ($row['status'] === 'pending') {
            // already pending - no action
        } elseif ($row['status'] === 'approved') {
            // already approved
        } else {
            // resend rejected request
            $resend = $connection->prepare("UPDATE event_registrations SET status='pending', registered_at=NOW() WHERE id=?");
            $resend->bind_param("i", $row['id']);
            $resend->execute();
        }
    }

    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?') . "?" . $_SERVER['QUERY_STRING']);
    exit();
}


// -----------------------------------------------------
//  LEAVE EVENT
// -----------------------------------------------------
if (isset($_POST['leave_event_id']) && $member_id && $_SESSION['role'] === 'member') {
    // Verify CSRF token
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


// -----------------------------------------------------
//  FILTER INPUTS
// -----------------------------------------------------
$q             = $_GET['q'] ?? '';
$filter_club   = !empty($_GET['club_id']) ? intval($_GET['club_id']) : null;
$section       = $_GET['section'] ?? 'upcoming';
$page          = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit         = 10;
$offset        = ($page - 1) * $limit;


// -----------------------------------------------------
//  BUILD WHERE + PARAMS
// -----------------------------------------------------
$where  = " WHERE 1=1 ";
$types  = "";
$params = [];

// search
if ($q !== "") {
    $where .= " AND (e.title LIKE ? OR c.name LIKE ?) ";
    $types .= "ss";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

// club
if ($filter_club !== null) {
    $where .= " AND e.club_id=? ";
    $types .= "i";
    $params[] = $filter_club;
}

// section
if ($section === "upcoming") {
    $where .= " AND e.date >= CURDATE() ";
} elseif ($section === "past") {
    $where .= " AND e.date < CURDATE() ";
}


// -----------------------------------------------------
//  COUNT FOR PAGINATION
// -----------------------------------------------------
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


// -----------------------------------------------------
//  FETCH EVENTS
// -----------------------------------------------------
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
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Events</title>
    <style>
        body{font-family:Arial;margin:28px;background:#f7f7f7;}
        .event{background:#fff;padding:14px;margin-bottom:14px;border-radius:6px;border:1px solid #ddd;}
        .btn{padding:8px 12px;border:none;border-radius:4px;cursor:pointer;}
        .btn-green{background:#28a745;color:white;}
        .btn-red{background:#d9534f;color:white;}
    </style>
</head>
<body>

<a href="dashboard.php" class="button">Back to Dashboard</a>
<h2>Events</h2>


<!-- SEARCH & FILTER FORM -->
<form method="GET" action="" style="margin-bottom:16px;">
    <input type="text" name="q" placeholder="Search title or club" 
           value="<?= htmlspecialchars($q) ?>">

    <select name="club_id">
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

    <select name="section">
        <option value="upcoming" <?= $section === "upcoming" ? "selected" : "" ?>>Upcoming</option>
        <option value="past"     <?= $section === "past" ? "selected" : "" ?>>Past</option>
        <option value="all"      <?= $section === "all" ? "selected" : "" ?>>All</option>
    </select>

    <button type="submit">Filter</button>
</form>


<!-- EVENTS LIST -->
<?php if ($events_result->num_rows === 0): ?>

    <p>No events found.</p>

<?php else: ?>

    <?php while ($e = $events_result->fetch_assoc()): ?>
        <div class="event">

            <?php if (!empty($e['event_image'])): ?>
                <img src="<?= htmlspecialchars($e['event_image']) ?>" 
                     style="width:120px;float:right;margin-left:12px;border-radius:6px;">
            <?php endif; ?>

            <h3><?= htmlspecialchars($e['title']) ?></h3>
            <p><strong>Club:</strong>
                <a href="club_details.php?id=<?= $e['club_id'] ?>">
                    <?= htmlspecialchars($e['club_name']) ?>
                </a>
            </p>

            <p><?= nl2br(htmlspecialchars($e['description'])) ?></p>

            <p>
                <strong>Date:</strong> <?= $e['date'] ?> &nbsp;
                <strong>Time:</strong> <?= $e['event_time'] ?>
            </p>

            <p><strong>Venue:</strong> <?= htmlspecialchars($e['venue']) ?></p>

            <p><strong>Registration deadline:</strong> <?= htmlspecialchars($e['registration_deadline']) ?></p>


            <!-- REGISTER / LEAVE BUTTON -->
            <?php
            $event_id = $e['event_id'];

            // Check registration status for this event
            $check = $connection->prepare("SELECT id, status FROM event_registrations WHERE event_id=? AND member_id=?");
            $check->bind_param("ii", $event_id, $member_id);
            $check->execute();
            $reg_result = $check->get_result();
            $registration_status = "";
            if ($reg_result->num_rows > 0) {
                $reg_row = $reg_result->fetch_assoc();
                $registration_status = $reg_row['status'];
            }

            // Check membership status for the event's club
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

            <?php if ($registration_status === "approved"): ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="leave_event_id" value="<?= $event_id ?>">
                    <button class="btn btn-red">Leave Event</button>
                </form>

            <?php elseif ($registration_status === "pending"): ?>
                <p style="color:orange; display:inline;">Registration request pending approval.</p>

            <?php elseif ($registration_status === "rejected"): ?>
                <p style="color:red; display:inline;">Your registration was rejected.</p>
                <form method="POST" style="display:inline; margin-left:8px;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="register_event_id" value="<?= $event_id ?>">
                    <button class="btn btn-green">Request Again</button>
                </form>

            <?php elseif ($member_id && $_SESSION['role'] === 'member' && $membership_status === 'approved'): ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="register_event_id" value="<?= $event_id ?>">
                    <button class="btn btn-green">Request Registration</button>
                </form>

            <?php elseif (!$member_id || $_SESSION['role'] !== 'member'): ?>
                <p style="color:orange;">Login to register for events.</p>

            <?php else: ?>
                <p style="color:orange;">You must be an approved member to register for events.</p>
            <?php endif; ?>

            <p style="font-size:12px;color:#666;margin-top:8px;">
                Posted <?= $e['created_at'] ?>
            </p>

            <div style="clear:both;"></div>
        </div>
    <?php endwhile; ?>

    <!-- PAGINATION -->
    <div style="margin-top:16px;">
        Page <?= $page ?> of <?= $totalPages ?> &nbsp;&nbsp;

        <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$page-1])) ?>">Previous</a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$page+1])) ?>">Next</a>
        <?php endif; ?>
    </div>

<?php endif; ?>

</body>
</html>
