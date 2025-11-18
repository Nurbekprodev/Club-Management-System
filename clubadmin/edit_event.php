<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

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
        redirectWithMessage("manage_events.php", "Event updated successfully!");
    } else {
        setError("Error updating event");
        header("Location: edit_event.php?id=$event_id");
        exit();
    }
}
?>

<main>
<div class="container mt-4">
    <div style="max-width: 600px; margin: 0 auto;">
        <a href="manage_events.php" class="btn btn-ghost mb-3">← Back to Events</a>

        <div class="card">
            <div class="card-header">Edit Event</div>
            <div class="card-body">
                <form action="edit_event.php?id=<?= $event_id ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="club_id">Club</label>
                        <select id="club_id" name="club_id" class="form-control" required>
                            <?php $clubList->data_seek(0); ?>
                            <?php while ($c = $clubList->fetch_assoc()): ?>
                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $event['club_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($event['title']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required><?= htmlspecialchars($event['description']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" class="form-control" value="<?= $event['date'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="event_time">Time</label>
                        <input type="time" id="event_time" name="event_time" class="form-control" value="<?= $event['event_time'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="venue">Venue</label>
                        <input type="text" id="venue" name="venue" class="form-control" value="<?= htmlspecialchars($event['venue']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="registration_deadline">Registration Deadline</label>
                        <input type="date" id="registration_deadline" name="registration_deadline" class="form-control" value="<?= $event['registration_deadline'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="max_participants">Max Participants</label>
                        <input type="number" id="max_participants" name="max_participants" class="form-control" value="<?= $event['max_participants'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Current Image</label>
                        <?php if ($event['event_image']): ?>
                            <img src="<?= $event['event_image'] ?>" alt="Event Image" style="max-width: 200px; border-radius: 6px; margin-bottom: 12px;">
                        <?php else: ?>
                            <p class="text-muted">No image uploaded.</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="event_image">Change Image (optional)</label>
                        <input type="file" id="event_image" name="event_image" class="form-control">
                    </div>

                    <button type="submit" name="update_event" class="btn btn-primary" style="width: 100%;">Update Event</button>
                </form>
            </div>
        </div>
    </div>
</div>
</main>

<?php include '../includes/footer.php'; ?>
