<?php
session_start();
include "../includes/database.php";
include "../includes/functions.php";
include "../includes/header.php";

// Only members can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: ../public/login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// Fetch only clubs where user is member (approved or pending)
$sql = "
    SELECT c.id, c.name, c.description, c.category, c.logo, m.status
    FROM club_members m
    JOIN clubs c ON m.club_id = c.id
    WHERE m.user_id = ?
    ORDER BY m.status DESC, c.name ASC
";

$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$res = $stmt->get_result();
?>

<main>
<div class="container mt-4">

<div class="d-flex justify-between items-center mb-4">
  <h2>My Clubs</h2>
  <a href="dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
</div>

<?php if ($res->num_rows === 0): ?>
    <div class="card">
      <p class="text-muted text-center">You have not joined any clubs yet. <a href="clubs.php">Browse clubs →</a></p>
    </div>
<?php else: ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">

    <?php while($club = $res->fetch_assoc()): ?>
        <div class="card">

            <?php if (!empty($club['logo'])): ?>
    <img src="<?= htmlspecialchars($club['logo']) ?>" 
         alt="Club logo" 
         class="card-img-top mb-3" 
         style="height: 180px; object-fit: cover;"
         onerror="this.onerror=null;this.src='../includes/images/default_img.jpeg';">
<?php else: ?>
    <img src="../includes/images/default_img.jpeg" 
         alt="Default club logo" 
         class="card-img-top mb-3" 
         style="height: 180px; object-fit: cover;">
<?php endif; ?>


            <h3 class="card-header"><?= htmlspecialchars($club['name']) ?></h3>
            <p class="text-muted mb-3"><?= htmlspecialchars($club['description']) ?></p>
            <p><strong>Category:</strong> <span class="badge badge-success"><?= htmlspecialchars($club['category']) ?></span></p>

            <!-- Status -->
            <div class="mt-3 mb-3">
              <?php if ($club['status'] === 'approved'): ?>
                <span class="badge badge-success">✓ Member</span>
              <?php else: ?>
                <span class="badge badge-warning">⏳ Pending Approval</span>
              <?php endif; ?>
            </div>

            <a href="club_details.php?id=<?= $club['id'] ?>" class="btn btn-primary">View Details</a>
        </div>
    <?php endwhile; ?>

</div>

<?php endif; ?>

</div>
</main>

<?php include '../includes/footer.php'; ?>
