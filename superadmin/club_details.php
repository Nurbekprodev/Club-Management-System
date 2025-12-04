<?php
session_start();
include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

redirectIfNotSuperadmin();

$club_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($club_id <= 0) {
    redirectWithMessage("manage_clubs.php", "Invalid club ID.", true);
}

// Fetch club info
$stmt = $connection->prepare("SELECT clubs.*, users.name AS admin_name, users.email AS admin_email 
                              FROM clubs 
                              JOIN users ON clubs.created_by = users.id 
                              WHERE clubs.id = ?");
$stmt->bind_param("i", $club_id);
$stmt->execute();
$club = $stmt->get_result()->fetch_assoc();

if (!$club) {
    redirectWithMessage("manage_clubs.php", "Club not found.", true);
}

// Club logo
$club_logo_path = !empty($club['logo']) && file_exists("../uploads/club_images/" . $club['logo'])
    ? "../uploads/club_images/" . $club['logo']
    : "../uploads/club_images/default_club.jpg";

// Fetch events for this club
$events_stmt = $connection->prepare("SELECT * FROM events WHERE club_id=? ORDER BY date ASC, event_time ASC");
$events_stmt->bind_param("i", $club_id);
$events_stmt->execute();
$events = $events_stmt->get_result();
?>

<main>
<div class="container mt-4" style="max-width: 800px; margin: 0 auto;">

    <a href="manage_clubs.php" class="btn btn-ghost mb-3">← Back to Clubs</a>

    <div class="card mb-4">
        <img src="<?= htmlspecialchars($club_logo_path) ?>" alt="<?= htmlspecialchars($club['name']) ?> Logo" 
             class="card-img-top" 
             style="height:180px; width: 250px;  border-radius:4px;">

        <div class="card-body">
            <h2 class="card-header mb-3"><?= htmlspecialchars($club['name']) ?></h2>

            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($club['description'])) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($club['category']) ?></p>

            <hr>
            <p><strong>Created By:</strong> <?= htmlspecialchars($club['admin_name']) ?> (<?= htmlspecialchars($club['admin_email']) ?>)</p>
            <p><strong>Created At:</strong> <?= htmlspecialchars($club['created_at']) ?></p>
        </div>

        
    </div>

    <div class="card">
        <div class="card-header">Events in this Club</div>
        <div class="card-body">
            <?php if ($events->num_rows > 0): ?>
                <ul>
                    <?php while($event = $events->fetch_assoc()): ?>
                        <li>
                            <strong><?= htmlspecialchars($event['title']) ?></strong> 
                            (<?= htmlspecialchars($event['date']) ?> <?= htmlspecialchars($event['event_time']) ?>) 
                            at <?= htmlspecialchars($event['venue']) ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">No events found for this club.</p>
            <?php endif; ?>
        </div>
    </div>

</div>
</main>

<?php include '../includes/footer.php'; ?>
