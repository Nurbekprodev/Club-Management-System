<?php
session_start();

// Restrict access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

// Connect to database
include '../includes/database.php';

// Handle add club form
if (isset($_POST['add_club'])) {
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $created_by = $_SESSION['user_id']; // assuming you stored user_id in session

    $insert = "INSERT INTO clubs (name, description, created_by) VALUES ('$name', '$description', '$created_by')";
    if (mysqli_query($connection, $insert)) {
        $success_message = "Club added successfully!";
    } else {
        $error_message = "Error adding club.";
    }
}

// Fetch all clubs created by this clubadmin
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM clubs WHERE created_by = '$user_id'";
$result = mysqli_query($connection, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Clubs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f7f7f7;
        }
        h2 {
            color: #333;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
        }
        form {
            background: #fff;
            padding: 15px;
            border: 1px solid #ccc;
            width: 400px;
        }
        input, textarea {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
        }
        button {
            padding: 8px 16px;
            cursor: pointer;
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
        .actions a {
            margin-right: 10px;
        }
    </style>
</head>
<body>

<h2>Manage Clubs</h2>

<!-- Messages -->
<?php if (isset($success_message)): ?>
    <div class="message success"><?= $success_message ?></div>
<?php elseif (isset($error_message)): ?>
    <div class="message error"><?= $error_message ?></div>
<?php endif; ?>

<!-- Add Club Form -->
<form method="POST" action="">
    <h3>Add New Club</h3>
    <input type="text" name="name" placeholder="Club Name" required>
    <textarea name="description" placeholder="Club Description" rows="4" required></textarea>
    <button type="submit" name="add_club">Add Club</button>
</form>

<!-- Clubs Table -->
<table>
    <tr>
        <th>ID</th>
        <th>Club Name</th>
        <th>Description</th>
        <th>Actions</th>
    </tr>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td class="actions">
                    <a href="edit_club.php?id=<?= $row['id'] ?>">Edit</a>
                    <a href="delete_club.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="4">No clubs found.</td></tr>
    <?php endif; ?>
</table>

</body>
</html>
