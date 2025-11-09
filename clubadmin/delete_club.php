<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

if (!isset($_GET['id'])) {
    header("Location: manage_clubs.php");
    exit();
}

$club_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$query = "DELETE FROM clubs WHERE id='$club_id' AND created_by='$user_id'";
mysqli_query($connection, $query);

header("Location: manage_clubs.php?success=Club deleted successfully");
exit();
?>
