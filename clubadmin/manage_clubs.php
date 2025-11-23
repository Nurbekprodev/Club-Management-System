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

// Fetch clubs created by this admin (Prepared Statement)
$stmt = $connection->prepare("SELECT id, name, description FROM clubs WHERE created_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$clubs = $stmt->get_result();
?>

<main>
    <div class="container mt-4">

        <!-- Page Header -->
        <div class="page-header">
            <h2 class="page-title">Manage Clubs</h2>
            <div class="page-actions">
                <a href="create_club.php" class="btn btn-primary">+ Create New Club</a>
                <a href="dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
            </div>
        </div>

        <!-- Club Table -->
        <?php if ($clubs->num_rows > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Club Name</th>
                        <th>Description</th>
                        <th style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($club = $clubs->fetch_assoc()): ?>
                        <tr>
                            <td><?= $club['id'] ?></td>
                            <td><strong><?= htmlspecialchars($club['name']) ?></strong></td>
                            <td><?= htmlspecialchars(substr($club['description'], 0, 60)) ?>...</td>
                        <td class="actions-col">
                            <a href="view_club.php?id=<?= $club['id'] ?>" class="table-action-btn view">View</a>
                            <a href="edit_club.php?id=<?= $club['id'] ?>" class="table-action-btn edit">Edit</a>
                            <a href="delete_club.php?id=<?= $club['id'] ?>" class="table-action-btn delete" onclick="return confirm('Are you sure you want to delete this club?');">Delete</a>
                        </td>                          

                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        <?php else: ?>
            <div class="card text-center text-muted">
                <p>No clubs found. <a href="create_club.php">Create one to get started</a>.</p>
            </div>
        <?php endif; ?>

    </div>
</main>


<?php include '../includes/footer.php'; ?>
