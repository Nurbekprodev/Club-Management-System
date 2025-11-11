<?php
session_start();
include "../includes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: ../public/login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// Fetch approved clubs
$member_sql = "SELECT c.id, c.name 
               FROM clubs c
               JOIN club_members cm ON c.id = cm.club_id
               WHERE cm.user_id = ? AND cm.status='approved'";
$stmt = $connection->prepare($member_sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$clubs_result = $stmt->get_result();

if ($clubs_result->num_rows == 0) {
    echo "<h3>You are not a member of any club yet.</h3>";
    echo "<a href='clubs.php'>Join Clubs</a>";
    exit();
}

echo "<h2>Your Clubs and Their Events</h2>";
echo "<a href='dashboard.php'>Back to Dashboard</a><br><br>";

while ($club = $clubs_result->fetch_assoc()) {
    echo "<h3>" . htmlspecialchars($club['name']) . "</h3>";

    $event_sql = "SELECT * FROM events WHERE club_id=? ORDER BY date ASC";
    $event_stmt = $connection->prepare($event_sql);
    $event_stmt->bind_param("i", $club['id']);
    $event_stmt->execute();
    $events = $event_stmt->get_result();

    if ($events->num_rows == 0) {
        echo "<p>No events available.</p>";
    } else {
        echo "<ul>";
        while ($event = $events->fetch_assoc()) {
            echo "<li>";
            echo "<strong>" . htmlspecialchars($event['title']) . "</strong><br>";
            echo htmlspecialchars($event['description']) . "<br>";
            echo "Date: " . $event['date'];
            echo "</li><br>";
        }
        echo "</ul>";
    }
}
?>
