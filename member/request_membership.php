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
$success_message = $error_message = "";

// Handle request submission
if (isset($_POST['join_club'])) {
    $club_id = intval($_POST['club_id']);

    // Check if a row already exists in club_members for this user+club
    $checkSql = "SELECT id, status FROM club_members WHERE user_id = ? AND club_id = ?";
    $stmt = $connection->prepare($checkSql);
    $stmt->bind_param("ii", $member_id, $club_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0) {
        // Insert new request with status = pending
        $insertSql = "INSERT INTO club_members (club_id, user_id, status, created_at) VALUES (?, ?, 'pending', NOW())";
        $ins = $connection->prepare($insertSql);
        $ins->bind_param("ii", $club_id, $member_id);
        if ($ins->execute()) {
            $success_message = "Membership request sent successfully.";
        } else {
            $error_message = "Error sending request: " . $ins->error;
        }
    } else {
        $row = $res->fetch_assoc();
        if ($row['status'] === 'pending') {
            $error_message = "You already have a pending request for this club.";
        } elseif ($row['status'] === 'approved') {
            $error_message = "You are already a member of this club.";
        } elseif ($row['status'] === 'rejected') {
            // Allow re-request by updating the same row to pending
            $updateSql = "UPDATE club_members SET status='pending', created_at = NOW() WHERE id = ?";
            $upd = $connection->prepare($updateSql);
            $upd->bind_param("i", $row['id']);
            if ($upd->execute()) {
                $success_message = "Membership request re-submitted successfully.";
            } else {
                $error_message = "Error re-submitting request: " . $upd->error;
            }
        } else {
            $error_message = "You cannot request this club at the moment.";
        }
    }
}

// Fetch all clubs (to show list)
$clubsSql = "SELECT id, name, description FROM clubs ORDER BY name ASC";
$clubsResult = mysqli_query($connection, $clubsSql);

// Fetch the user's membership statuses from club_members
$statusSql = "SELECT club_id, status FROM club_members WHERE user_id = ?";
$stmt = $connection->prepare($statusSql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$statusRes = $stmt->get_result();

$club_status = [];
while ($r = $statusRes->fetch_assoc()) {
    $club_status[$r['club_id']] = $r['status'];
}
?>

<main>
<div class="container mt-4">

<div class="d-flex justify-between items-center mb-4">
  <h2>Join Clubs</h2>
  <a href="dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
</div>

<?php displayMessages(); ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">

<?php while ($club = mysqli_fetch_assoc($clubsResult)): ?>
    <div class="card">
        <h3 class="card-header mb-2"><?= htmlspecialchars($club['name']) ?></h3>
        <p class="text-muted mb-4"><?= htmlspecialchars($club['description']) ?></p>

        <div class="mt-3">
          <?php
          $cid = $club['id'];
          if (isset($club_status[$cid]) && $club_status[$cid] === 'approved'): ?>
              <button disabled class="btn" style="background:#ccc; cursor:not-allowed; width: 100%; padding: 10px;">✓ Member</button>
          <?php elseif (isset($club_status[$cid]) && $club_status[$cid] === 'pending'): ?>
              <button disabled class="btn" style="background:#ff9800; color:white; cursor:not-allowed; width: 100%; padding: 10px;">⏳ Request Pending</button>
          <?php elseif (isset($club_status[$cid]) && $club_status[$cid] === 'rejected'): ?>
              <form method="POST">
                  <input type="hidden" name="club_id" value="<?= $cid ?>">
                  <button type="submit" name="join_club" class="btn btn-primary" style="width: 100%; padding: 10px;">Request Again</button>
              </form>
          <?php else: ?>
              <form method="POST">
                  <input type="hidden" name="club_id" value="<?= $cid ?>">
                  <button type="submit" name="join_club" class="btn btn-primary" style="width: 100%; padding: 10px;">Request to Join</button>
              </form>
          <?php endif; ?>
        </div>
    </div>
<?php endwhile; ?>

</div>

</div>
</main>

<?php include '../includes/footer.php'; ?>
