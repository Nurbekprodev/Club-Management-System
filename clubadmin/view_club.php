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
    die("Invalid club ID.");
}

$club_id = intval($_GET['id']);

// Ensure club belongs to this admin
$stmt = $connection->prepare("SELECT * FROM clubs WHERE id = ? AND created_by = ?");
$stmt->bind_param("ii", $club_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Club not found or unauthorized access.");
}

$club = $result->fetch_assoc();
?>

<main>
<div class="container mt-4">
    <div style="max-width: 600px; margin: 0 auto;">
        <a href="manage_clubs.php" class="btn btn-ghost mb-3">← Back</a>

        <div class="card">
            <div class="card-header">Club Details</div>
            <div class="card-body">
                <?php if (!empty($club['logo'])): ?>
                    <img src="<?= $club['logo'] ?>" alt="Club Logo" style="width: 100%; max-width: 400px; border-radius: 8px; margin-bottom: 20px; object-fit: cover;">
                <?php endif; ?>

                <div class="mb-3">
                    <strong>Name:</strong>
                    <p class="text-muted"><?= htmlspecialchars($club['name']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Description:</strong>
                    <p class="text-muted"><?= htmlspecialchars($club['description']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Category:</strong>
                    <p class="text-muted"><?= htmlspecialchars($club['category']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Location:</strong>
                    <p class="text-muted"><?= htmlspecialchars($club['location']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Email:</strong>
                    <p class="text-muted"><?= htmlspecialchars($club['contact_email']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Phone:</strong>
                    <p class="text-muted"><?= htmlspecialchars($club['contact_phone']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Founded Year:</strong>
                    <p class="text-muted"><?= htmlspecialchars($club['founded_year']) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<?php include '../includes/footer.php'; ?>
