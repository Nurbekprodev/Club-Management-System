<?php
session_start();

// Allow only clubadmins
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';


// Count clubs created by this admin
$user_id = $_SESSION['user_id'];
$club_stmt = $connection->prepare("SELECT COUNT(*) AS total_clubs FROM clubs WHERE created_by = ?");
$club_stmt->bind_param("i", $user_id);
$club_stmt->execute();
$club_result = $club_stmt->get_result();
$total_clubs = $club_result->fetch_assoc()['total_clubs'] ?? 0;

// Count events created by this admin
$event_stmt = $connection->prepare("SELECT COUNT(*) AS total_events FROM events WHERE created_by = ?");
$event_stmt->bind_param("i", $user_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$total_events = $event_result->fetch_assoc()['total_events'] ?? 0;
?>

<main>
<div class="container mt-4">

<h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?>!</h2>
<p class="text-muted mb-4">You are logged in as <strong>Club Admin</strong>.</p>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
  <div class="card">
    <p class="text-muted mb-1">Clubs Created</p>
    <h3 style="color: var(--color-primary); margin: 0; font-size: 32px;"><?= $total_clubs ?></h3>
  </div>
  <div class="card">
    <p class="text-muted mb-1">Events Organized</p>
    <h3 style="color: var(--color-primary); margin: 0; font-size: 32px;"><?= $total_events ?></h3>
  </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
  <a href="manage_clubs.php" class="btn btn-primary">Manage Clubs</a>
  <a href="manage_events.php" class="btn btn-primary">Manage Events</a>
  <a href="manage_requests.php" class="btn btn-primary">Manage Requests</a>
  <a href="../public/logout.php" class="btn btn-ghost">Logout</a>
</div>

</div>
</main>

<?php include '../includes/footer.php'; ?>
