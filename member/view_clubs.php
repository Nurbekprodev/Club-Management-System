<?php
session_start();
include "../includes/database.php";
include "../includes/functions.php";
include "../includes/header.php";

$isMember = isset($_SESSION['user_id']) && $_SESSION['role'] === 'member';
$member_id = $isMember ? $_SESSION['user_id'] : null;

$filter = $_GET['filter'] ?? '';
$q = $_GET['q'] ?? '';
$category_filter = !empty($_GET['category']) ? $_GET['category'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

$where = " WHERE 1=1 ";
$params = [];
$types = "";

// Search
if ($q !== "") {
    $where .= " AND (name LIKE ? OR description LIKE ?) ";
    $types .= "ss";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

// Category filter
if ($category_filter !== "") {
    $where .= " AND category=? ";
    $types .= "s";
    $params[] = $category_filter;
}

// Only joined clubs
if ($filter === "joined" && $isMember) {
    $where .= " AND id IN (SELECT club_id FROM club_members WHERE user_id=? AND status='approved') ";
    $types .= "i";
    $params[] = $member_id;
}

// Count total
$countSql = "SELECT COUNT(*) AS total FROM clubs $where";
$stmt = $connection->prepare($countSql);
if ($types !== "") $stmt->bind_param($types, ...$params);
$stmt->execute();
$totalClubs = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = max(1, ceil($totalClubs / $limit));
$stmt->close();

// Fetch clubs
$sql = "SELECT * FROM clubs $where ORDER BY name ASC LIMIT ? OFFSET ?";
$stmt = $connection->prepare($sql);
if ($types !== "") {
    $types2 = $types . "ii";
    $params2 = array_merge($params, [$limit, $offset]);
    $stmt->bind_param($types2, ...$params2);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$clubsResult = $stmt->get_result();

// Fetch membership status
$club_status = [];
if ($isMember) {
    $statusSql = "SELECT club_id, status FROM club_members WHERE user_id=?";
    $stmt = $connection->prepare($statusSql);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
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

<!-- Search & Filters -->
<div class="card mb-4">
  <form method="GET" class="grid-4">

    <div class="form-group">
      <label>Search:</label>
      <input type="text" name="q" class="form-control" placeholder="Club name or description" value="<?= htmlspecialchars($q) ?>">
    </div>

    <div class="form-group">
      <label>Category:</label>
      <select name="category" class="form-control">
        <option value="">All Categories</option>
        <?php
        $catQuery = mysqli_query($connection, "SELECT DISTINCT category FROM clubs ORDER BY category ASC");
        while ($c = mysqli_fetch_assoc($catQuery)):
            $sel = ($category_filter == $c['category']) ? "selected" : "";
        ?>
            <option value="<?= htmlspecialchars($c['category']) ?>" <?= $sel ?>><?= htmlspecialchars($c['category']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Filter:</label>
      <select name="filter" class="form-control">
        <option value="">All Clubs</option>
        <?php if ($isMember): ?>
          <option value="joined" <?= $filter === 'joined' ? 'selected' : '' ?>>Joined Clubs</option>
        <?php endif; ?>
      </select>
    </div>

    <div class="form-group flex column justify-end">
      <button type="submit" class="btn btn-primary w-full">Search</button>
    </div>

  </form>
</div>

<!-- Club Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
<?php while($club = $clubsResult->fetch_assoc()): ?>
<div class="card">

    <img src="<?= !empty($club['logo']) ? htmlspecialchars($club['logo']) : '../includes/images/default_img.jpeg' ?>"
         alt="Club logo" 
         class="card-img-top mb-3" 
         style="height: 180px; object-fit: cover;"
         onerror="this.onerror=null;this.src='../includes/images/default_img.jpeg';">

    <h3 class="card-header"><?= htmlspecialchars($club['name']) ?></h3>
    <p class="text-muted mb-3"><?= htmlspecialchars($club['description']) ?></p>
    <p><strong>Category:</strong> <span class="badge badge-success"><?= htmlspecialchars($club['category']) ?></span></p>

    <div class="mt-3">
      <a href="club_details.php?id=<?= $club['id'] ?>" class="btn btn-outline mb-2">View Details</a>

      <?php if ($isMember): 
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
          else: ?>
        <a href="../public/login.php" class="btn btn-primary">Login to Join</a>
      <?php endif; ?>
    </div>

</div>
<?php endwhile; ?>
</div>

<!-- Pagination -->
<div class="pagination flex gap-2 mt-4 justify-center items-center">
<?php
$adjacents = 2;
$start = max(1, $page - $adjacents);
$end = min($totalPages, $page + $adjacents);
$getParams = $_GET;
?>

<?php if ($page > 1): $getParams['page'] = $page - 1; ?>
  <a href="?<?= http_build_query($getParams) ?>" class="btn btn-outline">← Previous</a>
<?php endif; ?>

<?php if ($start > 1): $getParams['page'] = 1; ?>
  <a href="?<?= http_build_query($getParams) ?>" class="btn btn-outline">1</a>
  <?php if ($start > 2): ?><span class="px-2">...</span><?php endif; ?>
<?php endif; ?>

<?php for ($i = $start; $i <= $end; $i++): $getParams['page'] = $i; ?>
  <a href="?<?= http_build_query($getParams) ?>" class="btn <?= ($i==$page)?'btn-primary':'btn-outline' ?>"><?= $i ?></a>
<?php endfor; ?>

<?php if ($end < $totalPages): $getParams['page'] = $totalPages; ?>
  <?php if ($end < $totalPages - 1): ?><span class="px-2">...</span><?php endif; ?>
  <a href="?<?= http_build_query($getParams) ?>" class="btn btn-outline"><?= $totalPages ?></a>
<?php endif; ?>

<?php if ($page < $totalPages): $getParams['page'] = $page + 1; ?>
  <a href="?<?= http_build_query($getParams) ?>" class="btn btn-outline">Next →</a>
<?php endif; ?>
</div>

</div>
</main>

<?php include '../includes/footer.php'; ?>
