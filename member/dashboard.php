<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: ../public/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Dashboard</title>
    <style>
        body { font-family: Arial; margin: 40px; background: #f7f7f7; }
        h2 { color: #333; }
        ul { list-style: none; padding: 0; }
        li { margin: 10px 0; }
        a.button {
            display: inline-block;
            background: #4CAF50;
            color: #fff;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?>!</h2>

<ul>
    <li><a href="request_role.php" class="button">Request Role</a></li>
    <li><a href="clubs.php" class="button">View Club</a></li>
    <li><a href="joined_clubs.php" class="button">My Joined Clubs</a></li>
    <li><a href="view_events.php" class="button">View Club Events</a></li>
    <li><a href="../public/logout.php" class="button">Logout</a></li>
</ul>

</body>
</html>
