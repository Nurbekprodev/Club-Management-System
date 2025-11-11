<?php
session_start();
include "../includes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

$clubadmin_id = $_SESSION['user_id'];

// Fetch clubs this admin created
$clubs = [];
$res = mysqli_query($connection, "SELECT id, name FROM clubs WHERE created_by='$clubadmin_id'");
while ($row = mysqli_fetch_assoc($res)) {
    $clubs[$row['id']] = $row['name'];
}

// Handle approve/reject
if (isset($_GET['action']) && isset($_GET['request_id'])) {
    $request_id = intval($_GET['request_id']);
    
    if ($_GET['action'] === 'approve') {
        $status = 'approved';
    } else {
        $status = NULL; // store NULL for rejected
    }

    $stmt = $connection->prepare("UPDATE club_members SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $request_id);
    $stmt->execute();

    header("Location: manage_requests.php");
    exit();
}

// Fetch pending requests for admin's clubs
$club_ids = implode(',', array_keys($clubs));
$sql = "SELECT cm.id, cm.user_id, cm.club_id, cm.status, cm.joined_at, u.name AS user_name, c.name AS club_name
        FROM club_members cm
        JOIN users u ON cm.user_id = u.id
        JOIN clubs c ON cm.club_id = c.id
        WHERE cm.status='pending' AND cm.club_id IN ($club_ids)
        ORDER BY cm.joined_at ASC";
$result = mysqli_query($connection, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Membership Requests</title>
<style>
body { font-family: Arial; margin:40px; }
table { width:100%; border-collapse: collapse; margin-top:20px; }
th, td { border:1px solid #ddd; padding:10px; text-align:left; }
th { background:#eee; }
a.button { padding:5px 10px; background:#4CAF50; color:#fff; text-decoration:none; border-radius:3px; }
a.reject { background:#f44336; }
a.back { display:inline-block; margin-top:10px; text-decoration:none; }
</style>
</head>
<body>

<h2>Pending Membership Requests</h2>
<a href="dashboard.php" class="back">← Back to Dashboard</a>

<?php if(mysqli_num_rows($result)==0): ?>
<p>No pending requests.</p>
<?php else: ?>
<table>
<tr>
<th>ID</th>
<th>User</th>
<th>Club</th>
<th>Requested At</th>
<th>Actions</th>
</tr>
<?php while($row=mysqli_fetch_assoc($result)): ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['user_name']) ?></td>
<td><?= htmlspecialchars($row['club_name']) ?></td>
<td><?= $row['joined_at'] ?></td>
<td>
<a href="?action=approve&request_id=<?= $row['id'] ?>" class="button">Approve</a>
<a href="?action=reject&request_id=<?= $row['id'] ?>" class="reject">Reject</a>
</td>
</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>

</body>
</html>
