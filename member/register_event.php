<?php
session_start();
include "../includes/database.php";

if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    die("Invalid event.");
}
$event_id = intval($_GET['event_id']);

// require login & role member
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    // redirect to login, pass redirect back
    $redirect = "register_event.php?event_id=" . $event_id;
    header("Location: ../public/login.php?redirect=" . urlencode($redirect));
    exit();
}
$user_id = $_SESSION['user_id'];

// fetch event
$stmt = $connection->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
if (!$event) { die("Event not found."); }

$today = date('Y-m-d');
if ($event['date'] < $today) {
    die("Event already passed.");
}
if ($event['registration_deadline'] < $today) {
    die("Registration deadline is over.");
}

// check membership approved for the club
$club_id = $event['club_id'];
$m = $connection->prepare("SELECT status FROM club_members WHERE user_id=? AND club_id=?");
$m->bind_param("ii", $user_id, $club_id);
$m->execute();
$mr = $m->get_result();
if ($mr->num_rows === 0) {
    // not a member -> redirect to club details page to join
    header("Location: club_details.php?id={$club_id}");
    exit();
}
$mrow = $mr->fetch_assoc();
if ($mrow['status'] !== 'approved') {
    die("You must be a club member (approved) to register. Current status: " . htmlspecialchars($mrow['status']));
}

// check if already registered
$chk = $connection->prepare("SELECT id FROM event_registrations WHERE event_id=? AND member_id=?");
$chk->bind_param("ii", $event_id, $user_id);
$chk->execute();
if ($chk->get_result()->num_rows > 0) {
    echo "You are already registered for this event. <a href='dashboard.php'>Back to Dashboard</a>";
    exit();
}

// optional: check max participants count
if (!empty($event['max_participants'])) {
    $count = $connection->prepare("SELECT COUNT(*) as c FROM event_registrations WHERE event_id=?");
    $count->bind_param("i", $event_id);
    $count->execute();
    $c = $count->get_result()->fetch_assoc()['c'];
    if ($c >= intval($event['max_participants'])) {
        die("Event is full.");
    }
}

// insert registration
$ins = $connection->prepare("INSERT INTO event_registrations (event_id, member_id) VALUES (?, ?)");
$ins->bind_param("ii", $event_id, $user_id);
if ($ins->execute()) {
    echo "Registered successfully. <a href='dashboard.php'>Back to Dashboard</a>";
} else {
    echo "Error registering: " . $ins->error;
} 