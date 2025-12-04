<?php
session_start();
include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

redirectIfNotSuperadmin();

// Pagination settings
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10; // adjust as needed
$offset = ($page - 1) * $limit;

// Count total users
$totalStmt = $connection->prepare("SELECT COUNT(*) AS total FROM users WHERE role != 'superadmin'");
$totalStmt->execute();
$totalUsers = $totalStmt->get_result()->fetch_assoc()['total'];
$totalPages = max(1, ceil($totalUsers / $limit));
$totalStmt->close();

// Fetch users
$stmt = $connection->prepare("SELECT * FROM users WHERE role != 'superadmin' ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<main>
<div class="container mt-4">

    <div class="d-flex justify-between items-center mb-4">
        <h2>Manage Users</h2>
        <a href="dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
    </div>

    <?php displayMessages(); ?>

    <?php if ($result->num_rows > 0): ?>
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['role']) ?></td>
                    <td>
                        <!-- View Profile -->
                        <a href="user_profile.php?id=<?= $row['id'] ?>" class="btn btn-outline" style="margin-right: 6px;">View Profile</a>

                        <!-- Delete User Form -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                            <input type="hidden" name="delete_user_id" value="<?= $row['id'] ?>">
                            <button type="submit" onclick="return confirm('Delete this user?')" class="btn btn-outline" style="color: var(--danger);">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
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

    <?php else: ?>
    <div class="card text-center text-muted">
        <p>No users found.</p>
    </div>
    <?php endif; ?>

</div>
</main>

<?php include '../includes/footer.php'; ?>
