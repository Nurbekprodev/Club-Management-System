<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'member') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION['user_name']; ?>!</h2>
    <ul>
        <li><a href="request_role.php">Request Role Upgrade</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</body>
</html>
