<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';
include '../includes/functions.php';

$admin_id = $_SESSION['user_id'];

// Handle form submission
if (isset($_POST['create_event'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $club_id = intval($_POST['club_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $date = $_POST['date'];
    $event_time = $_POST['event_time'];
    $venue = trim($_POST['venue']);
    $registration_deadline = $_POST['registration_deadline'];
    $max_participants = intval($_POST['max_participants']);
    $event_image = null;

    // Handle image upload
    if (!empty($_FILES['event_image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5000000; // 5MB
        $target_dir = "../includes/images/";
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['event_image']['tmp_name']);
        finfo_close($finfo);
        
        if (in_array($mime, $allowed_types) && $_FILES['event_image']['size'] <= $max_size) {
            $file_extension = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
            $safe_filename = bin2hex(random_bytes(8)) . '.' . $file_extension;
            $event_image = $target_dir . $safe_filename;
            if (!move_uploaded_file($_FILES['event_image']['tmp_name'], $event_image)) {
                echo "<script>alert('Failed to upload image');</script>";
                exit();
            }
        } else {
            echo "<script>alert('Invalid file type or file too large (max 5MB)');</script>";
            exit();
        }
    }

    // Insert new event
    $sql = "INSERT INTO events 
            (club_id, created_by, title, description, date, event_time, venue, registration_deadline, max_participants, event_image, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $connection->prepare($sql);
    $stmt->bind_param(
        "iissssssis",
        $club_id,
        $admin_id,
        $title,
        $description,
        $date,
        $event_time,
        $venue,
        $registration_deadline,
        $max_participants,
        $event_image
    );

    if ($stmt->execute()) {
        echo "<script>alert('Event created successfully!'); window.location='manage_events.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error creating event: " . $stmt->error . "');</script>";
    }
}

// Fetch clubs created by this admin (for dropdown)
$clubs = $connection->prepare("SELECT id, name FROM clubs WHERE created_by = ?");
$clubs->bind_param("i", $admin_id);
$clubs->execute();
$clubList = $clubs->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Event</title>
<style>
    body { font-family: Arial; margin: 40px; background: #f7f7f7; }
    form { background: #fff; padding: 20px; width: 450px; border: 1px solid #ccc; border-radius: 8px; }
    input, textarea, select { width: 100%; margin-bottom: 10px; padding: 8px; }
    button { padding: 10px 16px; background: #4CAF50; color: white; border: none; cursor: pointer; }
    a { text-decoration: none; color: #4CAF50; }
</style>
</head>
<body>

<h2>Create Event</h2>
<a href="manage_events.php">← Back to Events</a>

<form action="create_event.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    
    <label>Club:</label>
    <select name="club_id" required>
        <option value="">Select Club</option>
        <?php while ($c = $clubList->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Title:</label>
    <input type="text" name="title" required>

    <label>Description:</label>
    <textarea name="description" required></textarea>

    <label>Date:</label>
    <input type="date" name="date" required>

    <label>Time:</label>
    <input type="time" name="event_time" required>

    <label>Venue:</label>
    <input type="text" name="venue" required>

    <label>Registration Deadline:</label>
    <input type="date" name="registration_deadline" required>

    <label>Max Participants:</label>
    <input type="number" name="max_participants" required min="1">

    <label>Event Image:</label>
    <input type="file" name="event_image">

    <button type="submit" name="create_event">Create Event</button>
</form>

</body>
</html>
