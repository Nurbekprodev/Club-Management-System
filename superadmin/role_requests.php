<?php
session_start();

include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

redirectIfNotSuperadmin();

// Handle approve/deny actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id'], $_POST['action'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action'];
    
    // Validate action
    $action_error = validateAction($action, ['approve', 'deny']);
    if (!empty($action_error)) {
        setError($action_error);
        redirect("role_requests.php");
    }

    // Get request info
    $sql = $connection->prepare("SELECT * FROM role_requests WHERE id=?");
    $sql->bind_param("i", $request_id);
    $sql->execute();
    $result = $sql->get_result();
    $request = $result->fetch_assoc();

    if ($request) {
        $user_id = $request['user_id'];
        if ($action === 'approve') {
            // Update user role
            $upd = $connection->prepare("UPDATE users SET role=? WHERE id=?");
            $upd->bind_param("si", $request['requested_role'], $user_id);
            $upd->execute();
            $status = 'approved';
        } else {
            $status = 'denied';
        }

        // Update request status
        $upd_req = $connection->prepare("UPDATE role_requests SET status=? WHERE id=?");
        $upd_req->bind_param("si", $status, $request_id);
        $upd_req->execute();

        redirectWithMessage("role_requests.php", "Request has been $status.");
    } else {
        setError("Request not found.");
        redirect("role_requests.php");
    }
}

// Get all pending requests
$req_sql = $connection->prepare("
    SELECT rr.*, u.name, u.email 
    FROM role_requests rr 
    JOIN users u ON rr.user_id = u.id 
    WHERE rr.status='pending'
");
$req_sql->execute();
$requests = $req_sql->get_result();
?>

<main>
<div class="container mt-4">

    <div class="d-flex justify-between items-center mb-4">
        <h2>Pending Role Requests</h2>
        <a href="dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
    </div>

    <?php displayMessages(); ?>

    <?php if ($requests->num_rows > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Requested Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($r = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($r['name']); ?></td>
                    <td><?= htmlspecialchars($r['email']); ?></td>
                    <td><?= htmlspecialchars($r['requested_role']); ?></td>
                    <td>
                        <form method="POST" style="margin:0; display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                            <input type="hidden" name="request_id" value="<?= $r['id']; ?>">
                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                            <button type="submit" name="action" value="deny" class="btn btn-danger btn-sm">Deny</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="card text-center text-muted">
            <p>No pending requests.</p>
        </div>
    <?php endif; ?>

</div>
</main>

<?php include '../includes/footer.php'; ?>
