<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

$user_id = $_SESSION['user_id'];

// Fetch clubs created by this admin
$query = "SELECT * FROM clubs WHERE created_by = '$user_id'";
$result = mysqli_query($connection, $query);
$clubs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $clubs[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Clubs</title>
    <style>
        body { font-family: Arial; margin: 40px; background: #f7f7f7; }
        h2 { color: #333; }
        a.button { background: #4CAF50; color: #fff; padding: 8px 12px; text-decoration: none; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #eee; }
        .actions a { margin-right: 10px; }
    </style>
</head>
<body>

<h2>Manage Clubs</h2>

<a href="create_club.php" class="button">+ Create New Club</a>
<a href="dashboard.php" class="button" style="background: #777;">← Back to Dashboard</a>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Actions</th>
    </tr>

    <?php if (count($clubs) > 0): ?>
        <?php foreach ($clubs as $club): ?>
            <tr>
                <td><?= $club['id'] ?></td>
                <td><?= htmlspecialchars($club['name']) ?></td>
                <td><?= htmlspecialchars($club['description']) ?></td>
                <td class="actions">
                    <a href="edit_club.php?id=<?= $club['id'] ?>">Edit</a>
                    <a href="delete_club.php?id=<?= $club['id'] ?>" onclick="return confirm('Delete this club?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="4">No clubs found. Please create one.</td></tr>
    <?php endif; ?>
</table>

</body>
</html>
