<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include "../includes/database.php";
include "../includes/functions.php";
include "../includes/header.php";

if (!isset($_GET['id'])) {
    header("Location: manage_events.php");
    exit();
}

$event_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Verify ownership before deletion
$verify = $connection->prepare("SELECT id FROM events WHERE id = ? AND created_by = ?");
$verify->bind_param("ii", $event_id, $user_id);
$verify->execute();
$result = $verify->get_result();

if ($result->num_rows === 0) {
    redirectWithMessage("manage_events.php", "Event not found or unauthorized access.", true);
}

// Delete the event
$delete = $connection->prepare("DELETE FROM events WHERE id = ? AND created_by = ?");
$delete->bind_param("ii", $event_id, $user_id);

if ($delete->execute()) {
    redirectWithMessage("manage_events.php", "Event deleted successfully!");
} else {
    redirectWithMessage("manage_events.php", "Error deleting event.", true);
}
?>

<?php include '../includes/footer.php'; ?>
