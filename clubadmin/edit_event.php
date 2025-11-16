<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

$admin_id = $_SESSION['user_id'];

// Check if event ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage_events.php");
    exit();
}

$event_id = intval($_GET['id']);

// Fetch event details
$stmt = $connection->prepare("SELECT * FROM events WHERE id = ? AND created_by = ?");
$stmt->bind_param("ii", $event_id, $admin_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo "<script>alert('Event not found or access denied!'); window.location='manage_events.php';</script>";
    exit();
}

// Fetch clubs created by this admin (for dropdown)
$clubs = $connection->prepare("SELECT id, name FROM clubs WHERE created_by = ?");
$clubs->bind_param("i", $admin_id);
$clubs->execute();
$clubList = $clubs->get_result();

// Handle update
if (isset($_POST['update_event'])) {
    $club_id = intval($_POST['club_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $date = $_POST['date'];
    $event_time = $_POST['event_time'];
    $venue = trim($_POST['venue']);
    $registration_deadline = $_POST['registration_deadline'];
    $max_participants = intval($_POST['max_participants']);
    $event_image = $event['event_image']; // Keep old image by default

    // If new image uploaded
    if (!empty($_FILES['event_image']['name'])) {
        $target_dir = "../includes/images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES['event_image']['name']);
        if (move_uploaded_file($_FILES['event_image']['tmp_name'], $target_file)) {
            $event_image = $target_file;
        }
    }

    // Update query
    $sql = "UPDATE events 
            SET club_id = ?, title = ?, description = ?, date = ?, event_time = ?, venue = ?, 
                registration_deadline = ?, max_participants = ?, event_image = ?
            WHERE id = ? AND created_by = ?";

    $stmt = $connection->prepare($sql);
    $stmt->bind_param(
        "issssssissi",
        $club_id,
        $title,
        $description,
        $date,
        $event_time,
        $venue,
        $registration_deadline,
        $max_participants,
        $event_image,
        $event_id,
        $admin_id
    );

    if ($stmt->execute()) {
        echo "<script>alert('Event updated successfully!'); window.location='manage_events.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error updating event: " . $stmt->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Event</title>
<style>
    body { font-family: Arial; margin: 40px; background: #f7f7f7; }
    form { background: #fff; padding: 20px; width: 450px; border: 1px solid #ccc; border-radius: 8px; }
    input, textarea, select { width: 100%; margin-bottom: 10px; padding: 8px; }
    img { width: 100%; max-width: 200px; margin-bottom: 10px; border-radius: 8px; }
    button { padding: 10px 16px; background: #4CAF50; color: white; border: none; cursor: pointer; }
    a { text-decoration: none; color: #4CAF50; }
</style>
</head>
<body>

<h2>Edit Event</h2>
<a href="manage_events.php">← Back to Events</a>

<form action="edit_event.php?id=<?= $event_id ?>" method="POST" enctype="multipart/form-data">
    <label>Club:</label>
    <select name="club_id" required>
        <?php while ($c = $clubList->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>" <?= $c['id'] == $event['club_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Title:</label>
    <input type="text" name="title" value="<?= htmlspecialchars($event['title']) ?>" required>

    <label>Description:</label>
    <textarea name="description" required><?= htmlspecialchars($event['description']) ?></textarea>

    <label>Date:</label>
    <input type="date" name="date" value="<?= $event['date'] ?>" required>

    <label>Time:</label>
    <input type="time" name="event_time" value="<?= $event['event_time'] ?>" required>

    <label>Venue:</label>
    <input type="text" name="venue" value="<?= htmlspecialchars($event['venue']) ?>" required>

    <label>Registration Deadline:</label>
    <input type="date" name="registration_deadline" value="<?= $event['registration_deadline'] ?>" required>

    <label>Max Participants:</label>
    <input type="number" name="max_participants" value="<?= $event['max_participants'] ?>" required>

    <label>Current Image:</label><br>
    <?php if ($event['event_image']): ?>
        <img src="<?= $event['event_image'] ?>" alt="Event Image">
    <?php else: ?>
        <p>No image uploaded.</p>
    <?php endif; ?>

    <label>Change Image (optional):</label>
    <input type="file" name="event_image">

    <button type="submit" name="update_event">Update Event</button>
</form>

</body>
</html>
