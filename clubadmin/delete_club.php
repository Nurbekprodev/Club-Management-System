<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: manage_clubs.php");
    exit();
}

$club_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Verify ownership before deletion
$verify = $connection->prepare("SELECT id FROM clubs WHERE id = ? AND created_by = ?");
$verify->bind_param("ii", $club_id, $user_id);
$verify->execute();
$result = $verify->get_result();

if ($result->num_rows === 0) {
    redirectWithMessage("manage_clubs.php", "Club not found or unauthorized access.", true);
}

// Delete the club
$delete = $connection->prepare("DELETE FROM clubs WHERE id = ? AND created_by = ?");
$delete->bind_param("ii", $club_id, $user_id);

if ($delete->execute()) {
    redirectWithMessage("manage_clubs.php", "Club deleted successfully!");
} else {
    redirectWithMessage("manage_clubs.php", "Error deleting club.", true);
}
?>

<?php include '../includes/footer.php'; ?>
