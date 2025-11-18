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

// Initialize form values
$club_id = $_POST['club_id'] ?? '';
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$date = $_POST['date'] ?? '';
$event_time = $_POST['event_time'] ?? '';
$venue = $_POST['venue'] ?? '';
$registration_deadline = $_POST['registration_deadline'] ?? '';
$max_participants = $_POST['max_participants'] ?? '';

// Handle form submission
if (isset($_POST['create_event'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $club_id = intval($club_id);
    $title = sanitizeInput($title);
    $description = sanitizeInput($description);
    $date = $date;
    $event_time = $event_time;
    $venue = sanitizeInput($venue);
    $registration_deadline = $registration_deadline;
    $max_participants = intval($max_participants);

    // Validate inputs
    $title_error = validateEventTitle($title);
    $date_error = validateEventDate($date);
    $time_error = validateEventTime($event_time);
    $venue_error = validateVenue($venue);
    $max_error = validateMaxParticipants($max_participants);
    $deadline_error = validateRegistrationDeadline($registration_deadline, $date);
    
    if (!empty($title_error) || !empty($date_error) || !empty($time_error) || !empty($venue_error) || !empty($max_error) || !empty($deadline_error)) {
        setError($title_error ?: ($date_error ?: ($time_error ?: ($venue_error ?: ($max_error ?: $deadline_error)))));
        header("Location: create_event.php");
        exit();
    }

    $event_image = null;

    // Handle image upload
    if (!empty($_FILES['event_image']['name'])) {
        $upload_error = validateImageUpload($_FILES['event_image']);
        if (!empty($upload_error)) {
            setError($upload_error);
            header("Location: create_event.php");
            exit();
        }
        
        $target_dir = "../includes/images/";
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
        $safe_filename = bin2hex(random_bytes(8)) . '.' . $file_extension;
        $event_image = $target_dir . $safe_filename;
        if (!move_uploaded_file($_FILES['event_image']['tmp_name'], $event_image)) {
            setError("Failed to upload image");
            header("Location: create_event.php");
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
        redirectWithMessage("manage_events.php", "Event created successfully!");
    } else {
        setError("Error creating event. Please try again.");
        header("Location: create_event.php");
        exit();
    }
}

// Fetch clubs created by this admin (for dropdown)
$clubs = $connection->prepare("SELECT id, name FROM clubs WHERE created_by = ?");
$clubs->bind_param("i", $admin_id);
$clubs->execute();
$clubList = $clubs->get_result();
?>

<main>
<div class="container mt-4">
    <div style="max-width: 600px; margin: 0 auto;">
        <a href="manage_events.php" class="btn btn-ghost mb-3">← Back to Events</a>
        
        <div class="card">
            <div class="card-header">Create New Event</div>
            <div class="card-body">
                <?php displayMessages(); ?>

                <form action="create_event.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="club_id">Club</label>
                        <select id="club_id" name="club_id" class="form-control" required>
                            <option value="">Select Club</option>
                            <?php 
                            $clubList->data_seek(0);
                            while ($c = $clubList->fetch_assoc()): 
                            ?>
                                <option value="<?= $c['id'] ?>" <?= $club_id == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="event_time">Time</label>
                        <input type="time" id="event_time" name="event_time" class="form-control" value="<?php echo htmlspecialchars($event_time); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="venue">Venue</label>
                        <input type="text" id="venue" name="venue" class="form-control" value="<?php echo htmlspecialchars($venue); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="registration_deadline">Registration Deadline</label>
                        <input type="date" id="registration_deadline" name="registration_deadline" class="form-control" value="<?php echo htmlspecialchars($registration_deadline); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="max_participants">Max Participants</label>
                        <input type="number" id="max_participants" name="max_participants" class="form-control" value="<?php echo htmlspecialchars($max_participants); ?>" required min="1">
                    </div>

                    <div class="form-group">
                        <label for="event_image">Event Image</label>
                        <input type="file" id="event_image" name="event_image" class="form-control">
                        <small class="text-muted">Max 5MB, PNG/JPG/GIF</small>
                    </div>

                    <button type="submit" name="create_event" class="btn btn-primary" style="width: 100%;">Create Event</button>
                </form>
            </div>
        </div>
    </div>
</div>
</main>

<?php include '../includes/footer.php'; ?>
