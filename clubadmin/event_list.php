<?php
session_start();
include "../includes/database.php";
include "../includes/functions.php";
include "../includes/header.php";

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

<main>
<div class="container mt-4">

<div class="d-flex justify-between items-center mb-4">
  <h2>My Events</h2>
  <a href="dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
</div>

<?php if ($res->num_rows === 0): ?>
    <div class="card text-center text-muted">
      <p>No events created yet.</p>
    </div>
<?php else: ?>
    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Club</th>
            <th>Title</th>
            <th>Date</th>
            <th>Time</th>
            <th>Registrations</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $res->fetch_assoc()): ?>
          <tr>
              <td><?= htmlspecialchars($row['club_name']) ?></td>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= htmlspecialchars($row['date']) ?></td>
              <td><?= htmlspecialchars($row['event_time']) ?></td>
              <td><a href="manage_requests.php" class="btn btn-outline" style="padding: 4px 8px; font-size: 12px;">Manage Requests</a></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
<?php endif; ?>

</div>
</main>

<?php include '../includes/footer.php'; ?>
