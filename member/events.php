<?php
session_start();
include "../includes/database.php";

// logged-in info
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

// input (search/filter/pagination)
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_club = isset($_GET['club_id']) && is_numeric($_GET['club_id']) ? intval($_GET['club_id']) : null;
$section = isset($_GET['section']) ? $_GET['section'] : 'upcoming'; // upcoming | past | all
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// build WHERE dynamically using prepared statement parameters
$where = " WHERE 1=1 ";
$types = '';
$params = [];

// search by event title or club name
if ($q !== '') {
    $where .= " AND (e.title LIKE ? OR c.name LIKE ?) ";
    $types .= 'ss';
    $params[] = "%$q%";
    $params[] = "%$q%";
}

// filter by club
if ($filter_club !== null) {
    $where .= " AND e.club_id = ? ";
    $types .= 'i';
    $params[] = $filter_club;
}

// section filter
if ($section === 'upcoming') {
    $where .= " AND e.date >= CURDATE() ";
} elseif ($section === 'past') {
    $where .= " AND e.date < CURDATE() ";
} else {
    // 'all' -> no date restriction
}

// count total for pagination
$count_sql = "SELECT COUNT(*) AS total
              FROM events e
              JOIN clubs c ON e.club_id = c.id
              $where";
$stmt = $connection->prepare($count_sql);
if ($types !== '') {
    // bind params dynamically
    $bind_names = [];
    $bind_names[] = $types;
    for ($i=0;$i<count($params);$i++){
        $bind_names[] = &$params[$i];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = max(1, ceil($total / $limit));
$stmt->close();

// fetch events (with club name) with limit/offset
$sql = "SELECT e.*, c.name AS club_name, c.id AS club_id
        FROM events e
        JOIN clubs c ON e.club_id = c.id
        $where
        ORDER BY e.date ASC, e.event_time ASC
        LIMIT ? OFFSET ?";

$stmt = $connection->prepare($sql);
// build binding array
$bind_types = $types . 'ii';
$bind_params = array_merge($params, [$limit, $offset]);

$bind_names = [];
$bind_names[] = $bind_types;
for ($i=0;$i<count($bind_params);$i++){
    $bind_names[] = &$bind_params[$i];
}
call_user_func_array([$stmt, 'bind_param'], $bind_names);

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
        .event{background:#fff;padding:12px;margin-bottom:12px;border-radius:6px;border:1px solid #ddd;}
        .btn{padding:8px 12px;border-radius:4px;text-decoration:none;}
        .btn-register{background:#28a745;color:#fff;}
        .btn-disabled{background:#ccc;color:#fff;cursor:not-allowed;}
    </style>
</head>
<body>
<a href="dashboard.php" class="button">Back to Dashboard</a>
<h2>Events</h2>

<!-- Search & filters -->
<form method="GET" action="events.php" style="margin-bottom:16px;">
    <input type="text" name="q" placeholder="Search title or club" value="<?= htmlspecialchars($q) ?>">
    <select name="club_id">
        <option value="">All clubs</option>
        <?php
        $club_q = mysqli_query($connection, "SELECT id, name FROM clubs ORDER BY name ASC");
        while($c = mysqli_fetch_assoc($club_q)){
            $sel = ($filter_club !== null && $filter_club == $c['id']) ? 'selected' : '';
            echo "<option value=\"{$c['id']}\" $sel>".htmlspecialchars($c['name'])."</option>";
        }
        ?>
    </select>

    <select name="section">
        <option value="upcoming" <?= $section === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
        <option value="past" <?= $section === 'past' ? 'selected' : '' ?>>Past</option>
        <option value="all" <?= $section === 'all' ? 'selected' : '' ?>>All</option>
    </select>

    <button type="submit">Search</button>
</form>

<!-- Events list -->
<?php if ($events_result->num_rows === 0): ?>
    <p>No events found.</p>
<?php else: ?>
    <?php while($e = $events_result->fetch_assoc()): ?>
        <div class="event">
            <?php if (!empty($e['event_image'])): ?>
                <img src="<?= htmlspecialchars($e['event_image']) ?>" alt="" style="width:120px;float:right;margin-left:12px;border-radius:6px;">
            <?php endif; ?>

            <h3><?= htmlspecialchars($e['title']) ?></h3>
            <p><strong>Club:</strong> <a href="club_details.php?id=<?= $e['club_id'] ?>"><?= htmlspecialchars($e['club_name']) ?></a></p>
            <p><?= nl2br(htmlspecialchars($e['description'])) ?></p>
            <p>
                <strong>Date:</strong> <?= htmlspecialchars($e['date']) ?>
                &nbsp; <strong>Time:</strong> <?= htmlspecialchars($e['event_time']) ?>
            </p>
            <p><strong>Venue:</strong> <?= htmlspecialchars($e['venue']) ?></p>
            <p><strong>Registration deadline:</strong> <?= htmlspecialchars($e['registration_deadline']) ?></p>

            <?php
            // determine registration state
            $today = date('Y-m-d');
            $canRegister = ($e['date'] >= $today) && ($e['registration_deadline'] >= $today);
            if ($e['date'] < $today) {
                // past event
                echo '<span class="btn btn-disabled">Event passed</span>';
            } else {
                if (!$user_id || $role !== 'member') {
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

            <p style="font-size:12px;color:#666;margin-top:8px;">Posted <?= htmlspecialchars($e['created_at']) ?></p>
            <div style="clear:both;"></div>
        </div>
    <?php endwhile; ?>

    <!-- pagination -->
    <div style="margin-top:12px;">
        <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$page-1])) ?>">Previous</a>
        <?php endif; ?>

        Page <?= $page ?> of <?= $totalPages ?>

        <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$page+1])) ?>">Next</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

</body>
</html>
