<?php
session_start();
include "../includes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}
$admin_id = $_SESSION['user_id'];

$stmt = $connection->prepare("
    SELECT e.id, e.title, e.date, e.event_time, c.name AS club_name
    FROM events e
    JOIN clubs c ON e.club_id = c.id
    WHERE e.created_by = ?
    ORDER BY e.date DESC
");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>My Events</title></head>
<body>
<h2>My Events</h2>
<a href="dashboard.php">← Back</a>
<table border="1" cellpadding="6" cellspacing="0" style="margin-top:12px;">
<tr><th>Club</th><th>Title</th><th>Date</th><th>Time</th><th>Registrations</th></tr>
<?php while($row = $res->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['club_name']) ?></td>
    <td><?= htmlspecialchars($row['title']) ?></td>
    <td><?= htmlspecialchars($row['date']) ?></td>
    <td><?= htmlspecialchars($row['event_time']) ?></td>
    <td><a href="manage_event_registrations.php?event=<?= $row['id'] ?>">View Registrations</a></td>

</tr>
<?php endwhile; ?>
</table>
</body>
</html>
