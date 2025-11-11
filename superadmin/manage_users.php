<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

// Handle role update
if (isset($_POST['update_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    $query = "UPDATE users SET role = '$new_role' WHERE id = '$user_id'";
    mysqli_query($connection, $query);
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    mysqli_query($connection, "DELETE FROM users WHERE id = '$delete_id'");
    header("Location: manage_users.php");
    exit();
}

// Fetch users
$result = mysqli_query($connection, "SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            margin: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #eee;
        }
        form {
            display: inline;
        }
        button {
            cursor: pointer;
            padding: 5px 10px;
        }
        a {
            color: red;
            text-decoration: none;
        }
    </style>
</head>
<body>

<h2>Manage Users</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Action</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td>
            <form method="POST" action="">
                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                <select name="role">
                    <option value="student" <?= $row['role']=='student'?'selected':'' ?>>Student</option>
                    <option value="clubadmin" <?= $row['role']=='clubadmin'?'selected':'' ?>>Club Admin</option>
                    <option value="superadmin" <?= $row['role']=='superadmin'?'selected':'' ?>>Super Admin</option>
                </select>
                <button type="submit" name="update_role">Update</button>
            </form>
        </td>
        <td>
            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<p><a href="dashboard.php">Back to Dashboard</a></p>

</body>
</html>
