<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

$admin_id = $_SESSION['user_id'];

// Handle deletion
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    $del = $connection->prepare("DELETE FROM events WHERE id = ? AND created_by = ?");
    $del->bind_param("ii", $event_id, $admin_id);
    if ($del->execute()) {
        echo "<script>alert('Event deleted successfully'); window.location='manage_events.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error deleting event');</script>";
    }
}

// Fetch events created by this admin
$sql = "
    SELECT e.id, e.title, e.date, e.venue, e.registration_deadline, e.max_participants, e.event_image, 
           c.name AS club_name
    FROM events e
    JOIN clubs c ON e.club_id = c.id
    WHERE e.created_by = ?
    ORDER BY e.date DESC";

$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$events = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Events</title>
<style>
    body { font-family: Arial; margin: 40px; background: #f9f9f9; }
    table { border-collapse: collapse; width: 100%; background: #fff; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
    th { background: #f2f2f2; }
    a.button { padding: 8px 12px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
    a.delete-btn { color: red; text-decoration: none; }
    a.edit-btn { color: blue; text-decoration: none; }
</style>
</head>
<body>

<h2>Manage Events</h2>
<a href="dashboard.php" class="button">← Back to Dashboard</a>
<a href="create_event.php" class="button">+ Add New Event</a>
<br><br>

<?php if ($events->num_rows === 0): ?>
    <p>No events found. <a href="create_event.php">Create one</a>.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Club</th>
            <th>Title</th>
            <th>Date</th>
            <th>Venue</th>
            <th>Deadline</th>
            <th>Max Participants</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>

        <?php while($row = $events->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['club_name']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= htmlspecialchars($row['venue']) ?></td>
            <td><?= htmlspecialchars($row['registration_deadline']) ?></td>
            <td><?= htmlspecialchars($row['max_participants']) ?></td>
            <td>
                <?php if ($row['event_image']): ?>
                    <img src="<?= htmlspecialchars($row['event_image']) ?>" alt="Event Image" width="60">
                <?php else: ?>
                    No image
                <?php endif; ?>
            </td>
            <td>
    <a href="edit_event.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a> |
    <a href="manage_events.php?delete=<?= $row['id'] ?>" class="delete-btn"
       onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
</td>

        </tr>
        <?php endwhile; ?>
    </table>
<?php endif; ?>

</body>
</html>
