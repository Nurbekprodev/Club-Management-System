<?php
session_start();
include "../includes/database.php";

// Only members can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: ../public/login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// Fetch only clubs where user is member (approved or pending)
$sql = "
    SELECT c.id, c.name, c.description, c.category, c.logo, m.status
    FROM club_members m
    JOIN clubs c ON m.club_id = c.id
    WHERE m.user_id = ?
    ORDER BY m.status DESC, c.name ASC
";

$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$res = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>My Clubs</title>
<style>
body { font-family: Arial; margin: 40px; background:#f7f7f7; }
.club-card { border:1px solid #ccc; background:#fff; padding:12px; margin-bottom:12px; border-radius:5px; }
button, .btn { padding:8px 12px; cursor:pointer; border:none; border-radius:4px; }
.btn-secondary { background:#008CBA; color:#fff; }
.status-tag { padding:5px 9px; border-radius:4px; font-size:13px; }
.status-approved { background:#4CAF50; color:#fff; }
.status-pending { background:#ff9800; color:#fff; }
a.button { display:inline-block; margin-bottom:12px; padding:8px 12px; background:#4CAF50; color:#fff; text-decoration:none; border-radius:4px; }
</style>
</head>

<body>

<h2>My Clubs</h2>
<a href="dashboard.php" class="button">← Back to Dashboard</a>

<?php if ($res->num_rows === 0): ?>
    <p>You have not joined any clubs yet.</p>
<?php else: ?>

    <?php while($club = $res->fetch_assoc()): ?>
        <div class="club-card">

            <?php if (!empty($club['logo'])): ?>
                <img src="<?= htmlspecialchars($club['logo']) ?>" width="80" height="80"><br>
            <?php endif; ?>

            <h3><?= htmlspecialchars($club['name']) ?></h3>
            <p><?= htmlspecialchars($club['description']) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($club['category']) ?></p>

            <!-- Status -->
            <?php if ($club['status'] === 'approved'): ?>
                <span class="status-tag status-approved">Member</span>
            <?php else: ?>
                <span class="status-tag status-pending">Pending Approval</span>
            <?php endif; ?>

            <br><br>

            <!-- View Details -->
            <a href="club_details.php?id=<?= $club['id'] ?>" class="btn btn-secondary">View Details</a>

        </div>
    <?php endwhile; ?>

<?php endif; ?>

</body>
</html>
