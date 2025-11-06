<?php
session_start();

// Allow only club admins
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage_clubs.php");
    exit();
}

$club_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify ownership before deleting
$check = "SELECT * FROM clubs WHERE id='$club_id' AND created_by='$user_id'";
$result = mysqli_query($connection, $check);

if (mysqli_num_rows($result) == 0) {
    die("Club not found or you don’t have permission to delete it.");
}

// Delete the club
$delete = "DELETE FROM clubs WHERE id='$club_id' AND created_by='$user_id'";
if (mysqli_query($connection, $delete)) {
    header("Location: manage_clubs.php?success=deleted");
    exit();
} else {
    echo "Error deleting club.";
}
?>
