<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

if (empty($_SESSION['clubs']) || !is_array($_SESSION['clubs'])) {
    echo "You are not assigned to any club yet. Please create a club first.";
    exit();
}

include '../includes/database.php';

// Fetch all events for this admin’s clubs
$all_events = [];
foreach ($_SESSION['clubs'] as $club) {
    $club_id = $club['id'];
    $query = "SELECT * FROM events WHERE club_id = '$club_id' ORDER BY date ASC";
    $result = mysqli_query($connection, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $row['club_name'] = $club['name'];
        $all_events[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Events</title>
    <style>
        body { font-family: Arial; margin: 40px; background: #f7f7f7; }
        h2 { color: #333; }
        a.button { background: #4CAF50; color: #fff; padding: 8px 12px; text-decoration: none; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #eee; }
        .actions a { margin-right: 10px; }
    </style>
</head>
<body>

<h2>Manage Events</h2>

<a href="create_event.php" class="button">+ Create New Event</a>
<a href="dashboard.php" class="button" style="background: #777;">← Back to Dashboard</a>

<table>
    <tr>
        <th>ID</th>
        <th>Club</th>
        <th>Title</th>
        <th>Description</th>
        <th>Date</th>
        <th>Actions</th>
    </tr>

    <?php if (count($all_events) > 0): ?>
        <?php foreach ($all_events as $event): ?>
            <tr>
                <td><?= $event['id'] ?></td>
                <td><?= htmlspecialchars($event['club_name']) ?></td>
                <td><?= htmlspecialchars($event['title']) ?></td>
                <td><?= htmlspecialchars($event['description']) ?></td>
                <td><?= $event['date'] ?></td>
                <td class="actions">
                    <a href="edit_event.php?id=<?= $event['id'] ?>">Edit</a>
                    <a href="delete_event.php?id=<?= $event['id'] ?>" onclick="return confirm('Delete this event?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="6">No events found.</td></tr>
    <?php endif; ?>
</table>

</body>
</html>
