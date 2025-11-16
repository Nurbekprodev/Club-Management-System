<?php
session_start();
include "../includes/database.php";

if (!isset($_GET['id'])) {
    die("Club not found.");
}

$club_id = intval($_GET['id']);
$member_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

// Fetch club
$clubSql = "SELECT * FROM clubs WHERE id=?";
$stmt = $connection->prepare($clubSql);
$stmt->bind_param("i", $club_id);
$stmt->execute();
$club = $stmt->get_result()->fetch_assoc();

if (!$club) {
    die("Club not found.");
}

// Check membership
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

// Handle Join
if (isset($_POST['join_club']) && $member_id && $role === "member") {
    $join = $connection->prepare("INSERT INTO club_members (club_id, user_id, status, joined_at) VALUES (?, ?, 'pending', NOW())");
    $join->bind_param("ii", $club_id, $member_id);
    $join->execute();
    header("Location: club_details.php?id=" . $club_id);
    exit();
}

// Handle Leave
if (isset($_POST['leave_club']) && $membership_status === "approved") {
    $leave = $connection->prepare("DELETE FROM club_members WHERE id=?");
    $leave->bind_param("i", $membership_id);
    $leave->execute();
    header("Location: club_details.php?id=" . $club_id);
    exit();
}

// Pagination for events
$limit = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Count events
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

<h2><?= htmlspecialchars($club['name']) ?></h2>

<?php if ($club['logo']): ?>
    <img src="<?= htmlspecialchars($club['logo']) ?>" width="120"><br><br>
<?php endif; ?>

<p><b>Category:</b> <?= htmlspecialchars($club['category']) ?></p>
<p><b>Description:</b> <?= nl2br(htmlspecialchars($club['description'])) ?></p>
<p><b>Created At:</b> <?= $club['created_at'] ?></p>

<hr>

<h3>Membership</h3>

<?php if (!$member_id || $role !== "member"): ?>
    <p>Login as a member to join this club.</p>

<?php else: ?>

    <?php if ($membership_status === "approved"): ?>
        <p style="color:green"><b>You are a member of this club.</b></p>
        <form method="POST">
            <button type="submit" name="leave_club" style="background:red; color:white">Leave Club</button>
        </form>

    <?php elseif ($membership_status === "pending"): ?>
        <p style="color:orange"><b>Your membership request is pending.</b></p>

    <?php else: ?>
        <form method="POST">
            <button type="submit" name="join_club" style="background:green; color:white">Join Club</button>
        </form>
    <?php endif; ?>

<?php endif; ?>

<hr>

<h3>Club Events</h3>

<?php if ($events->num_rows == 0): ?>
    <p>No events yet.</p>

<?php else: ?>
    <?php while ($e = $events->fetch_assoc()): ?>
        <div style="border:1px solid #aaa; padding:12px; margin-bottom:12px;">

            <h4><?= htmlspecialchars($e['title']) ?></h4>

            <?php if ($e['event_image']): ?>
                <img src="<?= htmlspecialchars($e['event_image']) ?>" width="150"><br><br>
            <?php endif; ?>

            <p><?= nl2br(htmlspecialchars($e['description'])) ?></p>
            <p><b>Date:</b> <?= $e['date'] ?> (<?= $e['event_time'] ?>)</p>
            <p><b>Venue:</b> <?= htmlspecialchars($e['venue']) ?></p>
            <p><b>Registration Deadline:</b> <?= $e['registration_deadline'] ?></p>
            <p><b>Max Participants:</b> <?= $e['max_participants'] ?></p>

            <?php
            // determine registration state
            $today = date('Y-m-d');
            $canRegister = ($e['date'] >= $today) && ($e['registration_deadline'] >= $today);
            if ($e['date'] < $today) {
                // past event
                echo '<span class="btn btn-disabled">Event passed</span>';
            } else {
                if (!$member_id || $role !== 'member') {
                    // not a logged-in member
                    echo '<a class="btn btn-register" href="../public/login.php">Login to Register</a>';
                } else {
                    // logged-in member: link to register_event.php
                    if (!$canRegister) {
                        echo '<span class="btn btn-disabled">Registration closed</span>';
                    } else {
                        // show register link (register_event.php checks membership/duplicates)
                        echo '<a class="btn btn-register" href="register_event.php?event_id=' . $e['id'] . '">Register</a>';
                    }
                }
            }
            ?>

        </div>
    <?php endwhile; ?>

    <!-- Pagination -->
    <div>
        <?php if ($page > 1): ?>
            <a href="?id=<?= $club_id ?>&page=<?= $page - 1 ?>">Previous</a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?id=<?= $club_id ?>&page=<?= $page + 1 ?>">Next</a>
        <?php endif; ?>
    </div>

<?php endif; ?>

</body>
</html>
