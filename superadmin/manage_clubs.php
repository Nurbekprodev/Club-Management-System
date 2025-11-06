<?php
session_start();

// Allow only superadmins
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

// Handle delete request
if (isset($_GET['delete'])) {
    $club_id = $_GET['delete'];
    $delete = "DELETE FROM clubs WHERE id='$club_id'";
    mysqli_query($connection, $delete);
    header("Location: manage_clubs.php?success=deleted");
    exit();
}

// Fetch all clubs with creator info
$query = "SELECT clubs.*, users.name AS admin_name 
          FROM clubs 
          JOIN users ON clubs.created_by = users.id 
          ORDER BY clubs.id DESC";
$result = mysqli_query($connection, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage All Clubs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f7f7f7;
        }
        h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #eee;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            background: #d4edda;
            color: #155724;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<h2>All Clubs (Superadmin View)</h2>

<?php if (isset($_GET['success'])): ?>
    <div class="message">Club deleted successfully!</div>
<?php endif; ?>

<table>
    <tr>
        <th>ID</th>
        <th>Club Name</th>
        <th>Description</th>
        <th>Created By</th>
        <th>Actions</th>
    </tr>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= htmlspecialchars($row['admin_name']) ?></td>
                <td>
                    <a href="manage_clubs.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this club?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5">No clubs found.</td></tr>
    <?php endif; ?>
</table>

</body>
</html>
