<?php
session_start();
include "../includes/database.php";

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
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Request Membership</title>
<style>
    body { font-family: Arial; margin: 40px; }
    .club-card { border:1px solid #ccc; padding:12px; margin-bottom:12px; }
    button, .btn { padding:8px 12px; cursor:pointer; }
    .success { color: green; }
    .error { color: red; }
    a.button { display:inline-block; margin-bottom:12px; padding:8px 12px; background:#4CAF50; color:#fff; text-decoration:none; border-radius:4px; }
</style>
</head>
<body>

<h2>All Clubs</h2>
<a href="dashboard.php" class="button">Back to Dashboard</a>

<?php if ($success_message): ?><p class="success"><?= htmlspecialchars($success_message) ?></p><?php endif; ?>
<?php if ($error_message): ?><p class="error"><?= htmlspecialchars($error_message) ?></p><?php endif; ?>

<?php while ($club = mysqli_fetch_assoc($clubsResult)): ?>
    <div class="club-card">
        <h3><?= htmlspecialchars($club['name']) ?></h3>
        <p><?= htmlspecialchars($club['description']) ?></p>

        <?php
        $cid = $club['id'];
        if (isset($club_status[$cid]) && $club_status[$cid] === 'approved'): ?>
            <button disabled>Member</button>
        <?php elseif (isset($club_status[$cid]) && $club_status[$cid] === 'pending'): ?>
            <button disabled>Request Pending</button>
        <?php elseif (isset($club_status[$cid]) && $club_status[$cid] === 'rejected'): ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="club_id" value="<?= $cid ?>">
                <button type="submit" name="join_club">Request Again</button>
            </form>
        <?php else: ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="club_id" value="<?= $cid ?>">
                <button type="submit" name="join_club">Request to Join</button>
            </form>
        <?php endif; ?>
    </div>
<?php endwhile; ?>

</body>
</html>
