<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

$admin_id = $_SESSION['user_id'];

// Handle deletion
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    $del = $connection->prepare("DELETE FROM events WHERE id = ? AND created_by = ?");
    $del->bind_param("ii", $event_id, $admin_id);
    if ($del->execute()) {
        redirectWithMessage("manage_events.php", "Event deleted successfully!");
    } else {
        redirectWithMessage("manage_events.php", "Error deleting event.", true);
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

<main>
<div class="container mt-4">

<div class="d-flex justify-between items-center mb-4">
  <h2>Manage Events</h2>
  <div style="display: flex; gap: 8px;">
    <a href="create_event.php" class="btn btn-primary">+ Add New Event</a>
    <a href="dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
  </div>
</div>

<?php if ($events->num_rows === 0): ?>
    <div class="card text-center text-muted">
      <p>No events found. <a href="create_event.php">Create one</a>.</p>
    </div>
<?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Club</th>
          <th>Title</th>
          <th>Date</th>
          <th>Venue</th>
          <th>Deadline</th>
          <th>Max</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $events->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['club_name']) ?></td>
          <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
          <td><?= htmlspecialchars($row['date']) ?></td>
          <td><?= htmlspecialchars($row['venue']) ?></td>
          <td><?= htmlspecialchars($row['registration_deadline']) ?></td>
          <td><?= htmlspecialchars($row['max_participants']) ?></td>
          <td>
            <a href="view_event.php?id=<?= $row['id'] ?>" class="btn btn-outline" style="padding: 4px 8px; font-size: 12px;">View</a>
            <a href="edit_event.php?id=<?= $row['id'] ?>" class="btn btn-outline" style="padding: 4px 8px; font-size: 12px;">Edit</a>
            <a href="manage_events.php?delete=<?= $row['id'] ?>" class="btn btn-outline" style="padding: 4px 8px; font-size: 12px; color: #dc3545;" onclick="return confirm('Delete this event?');">Delete</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
<?php endif; ?>

</div>
</main>

<?php include '../includes/footer.php'; ?>
