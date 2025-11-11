<?php
session_start();
include "../includes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: ../public/login.php");
    exit();
}

$member_id = $_SESSION['user_id'];
$success = "";

// Leave club
if (isset($_POST['leave_club'])) {
    $club_id = $_POST['club_id'];

    $stmt = $connection->prepare("DELETE FROM club_members WHERE club_id=? AND user_id=?");
    $stmt->bind_param("ii", $club_id, $member_id);
    $stmt->execute();

    $success = "You left the club successfully.";
}

// Load joined clubs
$sql = "SELECT cm.club_id, c.name, c.description
        FROM club_members cm
        JOIN clubs c ON cm.club_id = c.id
        WHERE cm.user_id=? AND cm.status='approved'";

$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html>
<head>
<title>Joined Clubs</title>
<style>
.club-card { border:1px solid #ccc; padding:15px; margin-bottom:10px; }
button { padding:8px 12px; }
.success { color:green; }
</style>
</head>
<body>

<h2>Your Clubs</h2>
<a href="dashboard.php">← Back</a>

<?php if ($success) echo "<p class='success'>$success</p>"; ?>

<?php while($club = $result->fetch_assoc()): ?>
<div class="club-card">
    <h3><?= htmlspecialchars($club['name']) ?></h3>
    <p><?= htmlspecialchars($club['description']) ?></p>

    <form method="POST">
        <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">
        <button type="submit" name="leave_club">Leave Club</button>
    </form>
</div>
<?php endwhile; ?>

<?php if ($result->num_rows == 0): ?>
<p>You have not joined any clubs yet.</p>
<?php endif; ?>

</body>
</html>
