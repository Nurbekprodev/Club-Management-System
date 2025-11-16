<?php
session_start();
include "../includes/database.php";

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
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>All Clubs</title>
<style>
body { font-family: Arial; margin: 40px; background:#f7f7f7; }
.club-card { border:1px solid #ccc; background:#fff; padding:12px; margin-bottom:12px; border-radius:5px; }
button, .btn { padding:8px 12px; cursor:pointer; border:none; border-radius:4px; }
.btn-primary { background:#4CAF50; color:#fff; }
.btn-secondary { background:#008CBA; color:#fff; }
button[disabled] { background:#ccc; cursor:not-allowed; }
.success { color:green; }
.error { color:red; }
.pagination a { padding:8px 12px; margin:4px; background:#ddd; border-radius:4px; text-decoration:none; }
.pagination a.active { background:#4CAF50; color:white; }
a.button { display:inline-block; margin-bottom:12px; padding:8px 12px; background:#4CAF50; color:#fff; text-decoration:none; border-radius:4px; }
</style>
</head>
<body>

<h2>All Clubs</h2>
<a href="dashboard.php" class="button">Back to Dashboard</a>

<?php if (!empty($success_message)): ?><p class="success"><?= htmlspecialchars($success_message) ?></p><?php endif; ?>
<?php if (!empty($error_message)): ?><p class="error"><?= htmlspecialchars($error_message) ?></p><?php endif; ?>

<?php while($club = mysqli_fetch_assoc($clubsResult)): ?>
<div class="club-card">

    <?php if (!empty($club['logo'])): ?>
        <img src="<?= htmlspecialchars($club['logo']) ?>" width="80" height="80"><br>
    <?php endif; ?>

    <h3><?= htmlspecialchars($club['name']) ?></h3>
    <p><?= htmlspecialchars($club['description']) ?></p>
    <p><strong>Category:</strong> <?= htmlspecialchars($club['category']) ?></p>

    <!-- View Details -->
    <a href="club_details.php?id=<?= $club['id'] ?>" class="btn btn-secondary">View Details</a>

    <!-- Join Button Logic -->
    <?php if ($isMember): ?>
        <?php
        $cid = $club['id'];
        if (isset($club_status[$cid])) {
            $st = $club_status[$cid];

            if ($st === 'approved') {
                echo "<button disabled>Member</button>";
            } elseif ($st === 'pending') {
                echo "<button disabled>Request Pending</button>";
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
<?php endwhile; ?>

<!-- Pagination -->
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>">Previous</a>
    <?php endif; ?>

    <?php for ($i=1; $i <= $totalPages; $i++): ?>
        <a class="<?= ($i == $page) ? 'active' : '' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>">Next</a>
    <?php endif; ?>
</div>

</body>
</html>
