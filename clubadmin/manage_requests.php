<?php
session_start();
include "../includes/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

$clubadmin_id = $_SESSION['user_id'];

// Fetch clubs created by this admin
$clubs = [];
$res = mysqli_query($connection, "SELECT id, name FROM clubs WHERE created_by='$clubadmin_id'");
while($row = mysqli_fetch_assoc($res)){
    $clubs[$row['id']] = $row['name'];
}

// Handle approve/reject
if (isset($_GET['action']) && isset($_GET['request_id'])) {
    $request_id = intval($_GET['request_id']);
    $action = $_GET['action'] === 'approve' ? 'approved' : 'rejected';

    // Update membership status
    $stmt = $connection->prepare("UPDATE memberships SET status=? WHERE id=?");
    $stmt->bind_param("si", $action, $request_id);
    $stmt->execute();

    // If approved, add to club_members
    if($action === 'approved'){
        $stmt2 = $connection->prepare("SELECT user_id, club_id FROM memberships WHERE id=?");
        $stmt2->bind_param("i", $request_id);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $row = $res2->fetch_assoc();

        if($row){
            $stmt3 = $connection->prepare("INSERT INTO club_members (club_id, user_id, joined_at) VALUES (?, ?, NOW())");
            $stmt3->bind_param("ii", $row['club_id'], $row['user_id']);
            if(!$stmt3->execute()){
                die("Error adding to club_members: ".$stmt3->error);
            }
        }
    }

    header("Location: manage_requests.php");
    exit();
}

// Fetch pending requests for admin's clubs
$club_ids = implode(',', array_keys($clubs));
$sql = "SELECT m.id, m.user_id, m.club_id, m.status, m.created_at, u.name AS user_name, c.name AS club_name
        FROM memberships m
        JOIN users u ON m.user_id=u.id
        JOIN clubs c ON m.club_id=c.id
        WHERE m.status='pending' AND m.club_id IN ($club_ids)
        ORDER BY m.created_at ASC";
$result = mysqli_query($connection, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Membership Requests</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align:left; }
        th { background:#eee; }
        a.button { padding: 5px 10px; background:#4CAF50; color:#fff; text-decoration:none; border-radius:3px; }
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
        <td><?= $row['created_at'] ?></td>
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
