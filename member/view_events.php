<?php
session_start();
include "../includes/database.php";
include "../includes/functions.php";
include "../includes/header.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: ../public/login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// Fetch approved clubs
$member_sql = "SELECT c.id, c.name 
               FROM clubs c
               JOIN club_members cm ON c.id = cm.club_id
               WHERE cm.user_id = ? AND cm.status='approved'";
$stmt = $connection->prepare($member_sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$clubs_result = $stmt->get_result();

?>

<main>
<div class="container mt-4">

<div class="d-flex justify-between items-center mb-4">
  <h2>Your Clubs and Their Events</h2>
  <a href="dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
</div>

<?php if ($clubs_result->num_rows == 0): ?>
    <div class="card text-center text-muted">
      <p>You are not a member of any club yet.</p>
      <a href="clubs.php" class="btn btn-primary mt-3">Browse Clubs →</a>
    </div>
<?php else: ?>

<?php while ($club = $clubs_result->fetch_assoc()): ?>
  <div class="card mb-4">
    <h3 class="card-header mb-3"><?= htmlspecialchars($club['name']) ?></h3>

    <?php
    $event_sql = "SELECT * FROM events WHERE club_id=? ORDER BY date ASC";
    $event_stmt = $connection->prepare($event_sql);
    $event_stmt->bind_param("i", $club['id']);
    $event_stmt->execute();
    $events = $event_stmt->get_result();
    ?>

    <?php if ($events->num_rows == 0): ?>
      <p class="text-muted">No events available for this club.</p>
    <?php else: ?>
      <ul style="list-style: none; padding: 0; margin: 0;">
        <?php while ($event = $events->fetch_assoc()): ?>
          <li style="padding: 12px 0; border-bottom: 1px solid var(--border-color);">
            <strong><?= htmlspecialchars($event['title']) ?></strong><br>
            <p class="text-muted mb-1"><?= htmlspecialchars($event['description']) ?></p>
            <span class="text-muted" style="font-size: 12px;">📅 <?= $event['date'] ?></span>
          </li>
        <?php endwhile; ?>
      </ul>
    <?php endif; ?>
  </div>
<?php endwhile; ?>

<?php endif; ?>

</div>
</main>

<?php include '../includes/footer.php'; ?>
