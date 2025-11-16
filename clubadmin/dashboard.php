<?php
session_start();

// Allow only clubadmins
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

// Count clubs created by this admin
$user_id = $_SESSION['user_id'];
$club_query = "SELECT COUNT(*) AS total_clubs FROM clubs WHERE created_by = '$user_id'";
$club_result = mysqli_query($connection, $club_query);
$total_clubs = mysqli_fetch_assoc($club_result)['total_clubs'] ?? 0;

// Count events created by this admin
$event_query = "SELECT COUNT(*) AS total_events FROM events WHERE created_by = '$user_id'";
$event_result = mysqli_query($connection, $event_query);
$total_events = mysqli_fetch_assoc($event_result)['total_events'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Club Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 10px;
        }
        p {
            color: #555;
        }
        .stats {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
        }
        .card {
            flex: 1;
            margin: 10px;
            background: #f0f0f0;
            padding: 20px;
            border-radius: 6px;
            text-align: center;
        }
        .card h3 {
            margin: 10px 0;
            color: #333;
        }
        .links a {
            display: inline-block;
            margin-right: 15px;
            padding: 10px 20px;
            text-decoration: none;
            background: #007bff;
            color: white;
            border-radius: 5px;
        }
        .links a:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?>!</h2>
    <p>You are logged in as <strong>Club Admin</strong>.</p>

    <div class="stats">
        <div class="card">
            <h3><?= $total_clubs ?></h3>
            <p>Clubs Created</p>
        </div>
        <div class="card">
            <h3><?= $total_events ?></h3>
            <p>Events Organized</p>
        </div>
    </div>

    <div class="links">
        <a href="manage_clubs.php">Manage Clubs</a>
        <a href="manage_events.php">Manage Events</a>
        <a href="event_list.php" class="button">Manage Event Registrations</a>
        <a href="manage_requests.php">Manage Requests</a>
        <a href="../public/logout.php">Logout</a>
    </div>
</div>

</body>
</html>
