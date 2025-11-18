<?php
session_start();
include "../includes/database.php";
include "../includes/functions.php";
include "../includes/header.php";

// Determine if user is logged in as member
$isMember = isset($_SESSION['user_id']) && $_SESSION['role'] === 'member';
$member_id = $isMember ? $_SESSION['user_id'] : null;

// Handle join request (only if logged in)
if ($isMember && isset($_POST['join_club'])) {
    $club_id = intval($_POST['club_id']);
    $success_message = $error_message = "";

    // Check existing membership record
    $checkSql = "SELECT id, status FROM club_members WHERE user_id=? AND club_id=?";
    $stmt = $connection->prepare($checkSql);
    $stmt->bind_param("ii", $member_id, $club_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0) {
        // New request
        $insertSql = "INSERT INTO club_members (club_id, user_id, status, joined_at) VALUES (?, ?, 'pending', NOW())";
        $ins = $connection->prepare($insertSql);
        $ins->bind_param("ii", $club_id, $member_id);
        $ins->execute();
        $success_message = "Membership request sent successfully.";
    } else {
        $row = $res->fetch_assoc();

        if ($row['status'] === 'pending') {
            $error_message = "You already have a pending request.";
        } elseif ($row['status'] === 'approved') {
            $error_message = "You are already a member.";
        } else {
            // Re-submit rejected request
            $updateSql = "UPDATE club_members SET status='pending', joined_at=NOW() WHERE id=?";
            $upd = $connection->prepare($updateSql);
            $upd->bind_param("i", $row['id']);
            $upd->execute();
            $success_message = "Membership request re-submitted successfully.";
        }
    }
}

// Pagination setup
$limit = 3;   // Clubs per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Count total clubs
$countQuery = "SELECT COUNT(*) AS total FROM clubs";
$countResult = mysqli_query($connection, $countQuery);
$totalClubs = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalClubs / $limit);

// Fetch paginated clubs
$clubsSql = "SELECT id, name, description, category, logo FROM clubs ORDER BY name ASC LIMIT $limit OFFSET $offset";
$clubsResult = mysqli_query($connection, $clubsSql);

// Fetch membership status only if logged in as member
$club_status = [];
if ($isMember) {
    $statusSql = "SELECT club_id, status FROM club_members WHERE user_id=?";
    $stmt = $connection->prepare($statusSql);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $statusRes = $stmt->get_result();

    while ($r = $statusRes->fetch_assoc()) {
        $club_status[$r['club_id']] = $r['status'];
    }
}
?>

<main>
<div class="container mt-4">

<div class="d-flex justify-between items-center mb-4">
  <h2>All Clubs</h2>
  <a href="dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
</div>

<?php displayMessages(); ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">

<?php while($club = mysqli_fetch_assoc($clubsResult)): ?>
<div class="card">

    <?php if (!empty($club['logo'])): ?>
        <img src="<?= htmlspecialchars($club['logo']) ?>" alt="Club logo" class="card-img-top mb-3" style="height: 180px; object-fit: cover;">
    <?php endif; ?>

    <h3 class="card-header"><?= htmlspecialchars($club['name']) ?></h3>
    <p class="text-muted mb-3"><?= htmlspecialchars($club['description']) ?></p>
    <p><strong>Category:</strong> <span class="badge badge-success"><?= htmlspecialchars($club['category']) ?></span></p>

    <div class="mt-3">
      <a href="club_details.php?id=<?= $club['id'] ?>" class="btn btn-outline mb-2">View Details</a>

      <!-- Join Button Logic -->
      <?php if ($isMember): ?>
        <?php
        $cid = $club['id'];
        if (isset($club_status[$cid])) {
            $st = $club_status[$cid];
            if ($st === 'approved') {
                echo "<button disabled class='btn' style='background:#ccc; cursor:not-allowed;'>✓ Member</button>";
            } elseif ($st === 'pending') {
                echo "<button disabled class='btn' style='background:#ff9800; cursor:not-allowed; color:white;'>⏳ Request Pending</button>";
            } else {
                echo "<form method='POST' style='display:inline;'>
                        <input type='hidden' name='club_id' value='$cid'>
                        <button type='submit' name='join_club' class='btn btn-primary'>Join Club</button>
                      </form>";
            }
        } else {
            echo "<form method='POST' style='display:inline;'>
                    <input type='hidden' name='club_id' value='$cid'>
                    <button type='submit' name='join_club' class='btn btn-primary'>Join Club</button>
                  </form>";
        }
        ?>
      <?php else: ?>
        <a href="../public/login.php" class="btn btn-primary">Login to Join</a>
      <?php endif; ?>
    </div>
</div>
<?php endwhile; ?>

</div>

<!-- Pagination -->
<div class="mt-4" style="display: flex; gap: 8px;">
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>" class="btn btn-outline">← Previous</a>
  <?php endif; ?>

  <div style="flex: 1;"></div>

  <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page + 1 ?>" class="btn btn-outline">Next →</a>
  <?php endif; ?>
</div>

</div>
</main>

<?php include '../includes/footer.php'; ?>
