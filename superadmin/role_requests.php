<?php
session_start();

include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

redirectIfNotSuperadmin();

// handle approve/deny actions
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
        header("Location: role_requests.php");
        exit();
    }

    // get request info
    $sql = $connection->prepare("SELECT * FROM role_requests WHERE id=?");
    $sql->bind_param("i", $request_id);
    $sql->execute();
    $result = $sql->get_result();
    $request = $result->fetch_assoc();

    if ($request) {
        $user_id = $request['user_id'];
        if ($action == 'approve') {
            // update user role
            $upd = $connection->prepare("UPDATE users SET role=? WHERE id=?");
            $upd->bind_param("si", $request['requested_role'], $user_id);
            $upd->execute();
            $status = 'approved';
        } else {
            $status = 'denied';
        }

        // update request status
        $upd_req = $connection->prepare("UPDATE role_requests SET status=? WHERE id=?");
        $upd_req->bind_param("si", $status, $request_id);
        $upd_req->execute();
        redirectWithMessage("role_requests.php", "Request has been $status.");
    } else {
        setError("Request not found.");
        header("Location: role_requests.php");
        exit();
    }
}

// get all pending requests
$req_sql = $connection->prepare("SELECT rr.*, u.name, u.email FROM role_requests rr JOIN users u ON rr.user_id = u.id WHERE rr.status='pending'");
$req_sql->execute();
$requests = $req_sql->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Role Requests | Superadmin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <h2>Pending Role Requests</h2>

    <?php displayMessages(); ?>

    <?php if ($requests->num_rows > 0): ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>User Name</th>
                <th>Email</th>
                <th>Requested Role</th>
                <th>Action</th>
            </tr>
            <?php while ($r = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['name']); ?></td>
                    <td><?php echo htmlspecialchars($r['email']); ?></td>
                    <td><?php echo htmlspecialchars($r['requested_role']); ?></td>
                    <td>
                        <form method="POST" style="margin:0; display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                            <button type="submit" name="action" value="approve">Approve</button>
                            <button type="submit" name="action" value="deny">Deny</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No pending requests.</p>
    <?php endif; ?>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
