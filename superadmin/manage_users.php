<?php
session_start();

include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

redirectIfNotSuperadmin();

$valid_roles = ['member', 'clubadmin', 'superadmin'];

// Handle role update
if (isset($_POST['update_role'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['role'];
    
    // Validate role input
    $role_error = validateRole($new_role);
    if (!empty($role_error)) {
        setError($role_error);
    } else {
        $stmt = $connection->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $user_id);
        if ($stmt->execute()) {
            setSuccess("User role updated successfully.");
        } else {
            setError("Error updating user role.");
        }
    }
}

// Handle delete
if (isset($_POST['delete_user_id'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $delete_id = intval($_POST['delete_user_id']);
    if (deleteById($connection, "users", $delete_id)) {
        redirectWithMessage("manage_users.php", "User deleted successfully!");
    } else {
        redirectWithMessage("manage_users.php", "Error deleting user.", true);
    }
}

// Fetch users
$query = $connection->prepare("SELECT * FROM users ORDER BY id DESC");
$query->execute();
$result = $query->get_result();
?>

<main>
<div class="container mt-4">

    <div class="d-flex justify-between items-center mb-4">
        <h2>Manage Users</h2>
        <a href="dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
    </div>

    <?php displayMessages(); ?>

    <?php if ($result->num_rows > 0): ?>
    <table>
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
                    <!-- Update Role Form -->
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                        <select name="role" class="form-control" style="display:inline; width:auto;">
                            <option value="member" <?= $row['role'] === 'member' ? 'selected' : ''; ?>>Member</option>
                            <option value="clubadmin" <?= $row['role'] === 'clubadmin' ? 'selected' : ''; ?>>Club Admin</option>
                            <option value="superadmin" <?= $row['role'] === 'superadmin' ? 'selected' : ''; ?>>Superadmin</option>
                        </select>
                        <button type="submit" name="update_role" class="btn btn-outline" style="margin-left: 8px;">Update</button>
                    </form>

                    <!-- Delete User Form -->
                    <form method="POST" style="display:inline; margin-left:8px;">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                        <input type="hidden" name="delete_user_id" value="<?= $row['id'] ?>">
                        <button type="submit" onclick="return confirm('Delete this user?')" class="btn btn-outline" style="color: var(--danger);">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="card text-center text-muted">
        <p>No users found.</p>
    </div>
    <?php endif; ?>

</div>
</main>

<?php include '../includes/footer.php'; ?>
