<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include "../includes/database.php";

if (!isset($_GET['id'])) {
    header("Location: manage_events.php");
    exit();
}

$event_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if this event belongs to the logged-in club admin
$query = "SELECT * FROM events WHERE id = '$event_id' AND created_by = '$user_id'";
$result = mysqli_query($connection, $query);

if (mysqli_num_rows($result) == 0) {
    echo "Event not found or you don't have permission to delete it.";
    exit();
}

// Delete the event
$delete = "DELETE FROM events WHERE id = '$event_id' AND created_by = '$user_id'";

if (mysqli_query($connection, $delete)) {
    header("Location: manage_events.php?success=Event deleted successfully");
    exit();
} else {
    echo "Error deleting event.";
}
?>
