<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

$user_id = $_SESSION['user_id'];

// Fetch clubs created by this admin (Prepared Statement)
$stmt = $connection->prepare("SELECT id, name, description FROM clubs WHERE created_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$clubs = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Clubs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 40px;
        }
        h2 { margin-bottom: 15px; }

        .btn {
            padding: 8px 12px;
            background: #0066cc;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            margin-right: 10px;
        }
        .btn-secondary { background: #777; }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0px 3px 8px rgba(0,0,0,0.1);
        }
        th {
            background: #eef2f7;
            padding: 12px;
            font-weight: bold;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .actions a {
            margin-right: 12px;
            color: #0066cc;
            text-decoration: none;
        }
        .actions a.delete {
            color: #cc0000;
        }
        .actions a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<h2>Manage Clubs</h2>

<a href="create_club.php" class="btn">+ Create New Club</a>
<a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>

<table>
    <tr>
        <th>ID</th>
        <th>Club Name</th>
        <th>Description</th>
        <th>Actions</th>
    </tr>

    <?php if ($clubs->num_rows > 0): ?>
        <?php while ($club = $clubs->fetch_assoc()): ?>
            <tr>
                <td><?= $club['id'] ?></td>
                <td><?= htmlspecialchars($club['name']) ?></td>
                <td><?= htmlspecialchars($club['description']) ?></td>
                <td class="actions">
                    <a href="view_club.php?id=<?= $club['id'] ?>">View</a>
                    <a href="edit_club.php?id=<?= $club['id'] ?>">Edit</a>
                    <a href="delete_club.php?id=<?= $club['id'] ?>" class="delete"
                       onclick="return confirm('Are you sure you want to delete this club?');">
                       Delete
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="4" style="text-align:center;">No clubs found. Create one to get started.</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>
