<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    die("Invalid event ID.");
}

$event_id = intval($_GET['id']);

// Ensure event belongs to this admin
$stmt = $connection->prepare("
    SELECT e.*, c.name AS club_name
    FROM events e
    JOIN clubs c ON e.club_id = c.id
    WHERE e.id = ? AND e.created_by = ?
");
$stmt->bind_param("ii", $event_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Event not found or unauthorized access.");
}

$event = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>View Event</title>
<style>
    body { font-family: Arial; background: #f4f6f9; padding: 40px; }
    .container {
        background: #fff; padding: 25px; width: 500px;
        margin: auto; border-radius: 8px;
        box-shadow: 0px 3px 8px rgba(0,0,0,0.1);
    }
    h2 { margin-bottom: 15px; }
    .row { margin-bottom: 10px; }
    .label { font-weight: bold; color: #333; }
    .value { color: #555; }
    img { max-width: 140px; margin-top: 10px; border-radius: 6px; }
    a.btn { display: inline-block; padding: 8px 14px; background: #0066cc;
        color: #fff; text-decoration: none; border-radius: 5px; margin-top: 15px; }
</style>
</head>

<body>

<div class="container">
    <h2>Event Details</h2>

    <div class="row"><span class="label">Title:</span> <span class="value"><?= htmlspecialchars($event['title']) ?></span></div>
    <div class="row"><span class="label">Club:</span> <span class="value"><?= htmlspecialchars($event['club_name']) ?></span></div>
    <div class="row"><span class="label">Description:</span> <span class="value"><?= htmlspecialchars($event['description']) ?></span></div>
    <div class="row"><span class="label">Date:</span> <span class="value"><?= htmlspecialchars($event['created_at']) ?></span></div>
    <div class="row"><span class="label">Time:</span> <span class="value"><?= htmlspecialchars($event['event_time']) ?></span></div>
    <div class="row"><span class="label">Location:</span> <span class="value"><?= htmlspecialchars($event['venue']) ?></span></div>

    <?php if (!empty($event['image'])): ?>
        <div class="row"><span class="label">Image:</span><br>
            <img src="<?= $event['image'] ?>" alt="Event image">
        </div>
    <?php endif; ?>

    <a href="manage_events.php" class="btn">← Back</a>
</div>

</body>
</html>
