<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    die("Invalid event ID.");
}

$event_id = intval($_GET['id']);

// Ensure event belongs to this admin
$stmt = $connection->prepare("
    SELECT e.*, c.name AS club_name
    FROM events e
    JOIN clubs c ON e.club_id = c.id
    WHERE e.id = ? AND e.created_by = ?
");
$stmt->bind_param("ii", $event_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Event not found or unauthorized access.");
}

$event = $result->fetch_assoc();
?>

<main>
<div class="container mt-4">
    <div style="max-width: 600px; margin: 0 auto;">
        <a href="manage_events.php" class="btn btn-ghost mb-3">← Back</a>

        <div class="card">
            <div class="card-header">Event Details</div>
            <div class="card-body">
                <?php if (!empty($event['event_image'])): ?>
                    <img src="<?= htmlspecialchars($event['event_image']) ?>" alt="Event image" style="width: 100%; max-width: 400px; border-radius: 8px; margin-bottom: 15px;">
                <?php endif; ?>

                <div class="mb-3">
                    <strong>Title:</strong>
                    <p class="text-muted"><?= htmlspecialchars($event['title']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Club:</strong>
                    <p class="text-muted"><?= htmlspecialchars($event['club_name']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Description:</strong>
                    <p class="text-muted"><?= htmlspecialchars($event['description']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Date:</strong>
                    <p class="text-muted"><?= htmlspecialchars($event['date']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Time:</strong>
                    <p class="text-muted"><?= htmlspecialchars($event['event_time']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Venue:</strong>
                    <p class="text-muted"><?= htmlspecialchars($event['venue']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Registration Deadline:</strong>
                    <p class="text-muted"><?= htmlspecialchars($event['registration_deadline']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Max Participants:</strong>
                    <p class="text-muted"><?= htmlspecialchars($event['max_participants']) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<?php include '../includes/footer.php'; ?>
