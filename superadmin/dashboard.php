<?php
session_start();

// Only superadmin access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

// Fetch basic counts
$total_users_query = mysqli_query($connection, "SELECT COUNT(*) AS total FROM users");
$total_users = mysqli_fetch_assoc($total_users_query)['total'];

$total_clubs_query = mysqli_query($connection, "SELECT COUNT(*) AS total FROM clubs");
$total_clubs = mysqli_fetch_assoc($total_clubs_query)['total'];

$pending_requests_query = mysqli_query($connection, "SELECT COUNT(*) AS total FROM role_requests WHERE status = 'pending'");
$pending_requests = mysqli_fetch_assoc($pending_requests_query)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Superadmin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            margin: 40px;
        }
        h2 {
            color: #333;
        }
        .dashboard {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .card {
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 200px;
            text-align: center;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin: 10px 0;
            color: #555;
        }
        .card p {
            font-size: 24px;
            color: #007bff;
        }
        .links {
            margin-top: 30px;
        }
        .links a {
            margin-right: 20px;
            text-decoration: none;
            color: #007bff;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>Welcome, Superadmin</h2>

<div class="dashboard">
    <div class="card">
        <h3>Total Users</h3>
        <p><?= $total_users ?></p>
    </div>
    <div class="card">
        <h3>Total Clubs</h3>
        <p><?= $total_clubs ?></p>
    </div>
    <div class="card">
        <h3>Pending Role Requests</h3>
        <p><?= $pending_requests ?></p>
    </div>
</div>

<div class="links">
    <a href="manage_clubs.php">Manage Clubs</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="role_requests.php">View Role Requests</a>
    <a href="../public/logout.php">Logout</a>
</div>

</body>
</html>
