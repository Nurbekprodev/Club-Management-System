<?php
session_start();

include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

redirectIfNotSuperadmin();

// Handle delete request
if (isset($_POST['delete_club_id'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $club_id = intval($_POST['delete_club_id']);
    if (deleteById($connection, "clubs", $club_id)) {
        redirectWithMessage("manage_clubs.php", "Club deleted successfully!");
    } else {
        redirectWithMessage("manage_clubs.php", "Error deleting club.", true);
    }
}

$query = $connection->prepare("SELECT clubs.*, users.name AS admin_name FROM clubs JOIN users ON clubs.created_by = users.id ORDER BY clubs.id DESC");
$query->execute();
$result = $query->get_result();
?>

<main>
<div class="container mt-4">

<div class="d-flex justify-between items-center mb-4">
  <h2>All Clubs (Superadmin View)</h2>
  <a href="dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
</div>

<?php displayMessages(); ?>

<?php if ($result->num_rows > 0): ?>
    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Club Name</th>
            <th>Description</th>
            <th>Created By</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
              <td><?= htmlspecialchars(substr($row['description'], 0, 60)) ?>...</td>
              <td><?= htmlspecialchars($row['admin_name']) ?></td>
              <td>
                  <!-- View club details (Superadmin version) -->
                  <a href="club_details.php?id=<?= $row['id'] ?>" class="table-action-btn view" style="padding: 4px 8px; font-size: 12px; margin-left:4px;">
                      View Details
                  </a>
                  <form method="POST" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                      <input type="hidden" name="delete_club_id" value="<?= $row['id'] ?>">
                      <button type="submit" onclick="return confirm('Delete this club?')" class="table-action-btn delete" style="padding: 4px 8px; font-size: 12px; ">Delete</button>
                  </form>
              </td>
                          
             
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
<?php else: ?>
  <div class="card text-center text-muted">
    <p>No clubs found.</p>
  </div>
<?php endif; ?>

</div>
</main>

<?php include '../includes/footer.php'; ?>
