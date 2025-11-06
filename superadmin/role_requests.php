<?php
session_start();
include '../includes/database.php';

// check if logged in and superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../public/login.php");
    exit();
}

$success = "";

// handle approve/deny actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id'], $_POST['action'])) {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action'];

    // get request info
    $sql = "SELECT * FROM role_requests WHERE id=$request_id";
    $result = mysqli_query($connection, $sql);
    $request = mysqli_fetch_assoc($result);

    if ($request) {
        $user_id = $request['user_id'];
        if ($action == 'approve') {
            // update user role
            mysqli_query($connection, "UPDATE users SET role='{$request['requested_role']}' WHERE id=$user_id");
            $status = 'approved';
        } else {
            $status = 'denied';
        }

        // update request status
        mysqli_query($connection, "UPDATE role_requests SET status='$status' WHERE id=$request_id");
        $success = "Request has been $status.";
    }
}

// get all pending requests
$requests = mysqli_query($connection, "SELECT rr.*, u.name, u.email FROM role_requests rr JOIN users u ON rr.user_id = u.id WHERE rr.status='pending'");
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

    <?php if ($success): ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <?php if (mysqli_num_rows($requests) > 0): ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>User Name</th>
                <th>Email</th>
                <th>Requested Role</th>
                <th>Action</th>
            </tr>
            <?php while ($r = mysqli_fetch_assoc($requests)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['name']); ?></td>
                    <td><?php echo htmlspecialchars($r['email']); ?></td>
                    <td><?php echo $r['requested_role']; ?></td>
                    <td>
                        <form method="POST" style="margin:0;">
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
