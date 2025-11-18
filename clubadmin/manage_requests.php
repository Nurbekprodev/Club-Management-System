<?php
session_start();

// Allow only clubadmins
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';
include '../includes/functions.php';

$admin_id = $_SESSION['user_id'];
$success_message = $error_message = "";

// =====================
// HANDLE MEMBERSHIP REQUESTS
// =====================
if (isset($_POST['action']) && isset($_POST['request_id'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $action = $_POST['action']; // 'approve' or 'reject'
    $request_id = intval($_POST['request_id']);

    // Verify this request belongs to a club created by this admin
    $verify = $connection->prepare("
        SELECT cm.id, cm.club_id FROM club_members cm
        JOIN clubs c ON cm.club_id = c.id
        WHERE cm.id = ? AND c.created_by = ?
    ");
    $verify->bind_param("ii", $request_id, $admin_id);
    $verify->execute();
    $verify_result = $verify->get_result();

    if ($verify_result->num_rows > 0) {
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        $update = $connection->prepare("UPDATE club_members SET status = ? WHERE id = ?");
        $update->bind_param("si", $status, $request_id);
        $update->execute();
        $success_message = "Request " . $status . " successfully!";
    } else {
        $error_message = "Unauthorized action.";
    }
}

// =====================
// HANDLE EVENT REGISTRATION REQUESTS
// =====================
if (isset($_POST['action_event']) && isset($_POST['event_request_id'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $action_event = $_POST['action_event']; // 'approve' or 'reject'
    $event_request_id = intval($_POST['event_request_id']);

    // Verify this event registration belongs to an event created by this admin
    $verify_event = $connection->prepare("
        SELECT er.id, er.event_id FROM event_registrations er
        JOIN events e ON er.event_id = e.id
        WHERE er.id = ? AND e.created_by = ?
    ");
    $verify_event->bind_param("ii", $event_request_id, $admin_id);
    $verify_event->execute();
    $verify_event_result = $verify_event->get_result();

    if ($verify_event_result->num_rows > 0) {
        $event_status = ($action_event === 'approve') ? 'approved' : 'rejected';
        $update_event = $connection->prepare("UPDATE event_registrations SET status = ? WHERE id = ?");
        $update_event->bind_param("si", $event_status, $event_request_id);
        $update_event->execute();
        $success_message = "Event registration " . $event_status . " successfully!";
    } else {
        $error_message = "Unauthorized action.";
    }
}

// =====================
// FETCH PENDING MEMBERSHIP REQUESTS
// =====================
$pending_members = $connection->prepare("
    SELECT cm.id, cm.club_id, cm.user_id, u.name, u.email, c.name as club_name, cm.joined_at
    FROM club_members cm
    JOIN users u ON cm.user_id = u.id
    JOIN clubs c ON cm.club_id = c.id
    WHERE c.created_by = ? AND cm.status = 'pending'
    ORDER BY cm.joined_at DESC
");
$pending_members->bind_param("i", $admin_id);
$pending_members->execute();
$pending_members_result = $pending_members->get_result();

// =====================
// FETCH PENDING EVENT REGISTRATIONS
// =====================
$pending_events = $connection->prepare("
    SELECT er.id, er.event_id, er.member_id, u.name, u.email, e.title, c.name as club_name, er.registered_at
    FROM event_registrations er
    JOIN users u ON er.member_id = u.id
    JOIN events e ON er.event_id = e.id
    JOIN clubs c ON e.club_id = c.id
    WHERE e.created_by = ? AND er.status = 'pending'
    ORDER BY er.registered_at DESC
");
$pending_events->bind_param("i", $admin_id);
$pending_events->execute();
$pending_events_result = $pending_events->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Requests</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f7f7f7; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        h3 { color: #555; margin-top: 30px; }
        .request-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; background: #f9f9f9; }
        .request-card p { margin: 5px 0; }
        .button-group { margin-top: 10px; }
        .btn { padding: 8px 12px; margin-right: 10px; border: none; border-radius: 4px; cursor: pointer; color: white; }
        .btn-approve { background: #4CAF50; }
        .btn-approve:hover { background: #45a049; }
        .btn-reject { background: #f44336; }
        .btn-reject:hover { background: #da190b; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        a.back-btn { display: inline-block; margin-bottom: 20px; padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        a.back-btn:hover { background: #0056b3; }
        .no-requests { color: #888; font-style: italic; }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    
    <h2>Manage Requests</h2>

    <?php if (!empty($success_message)): ?>
        <div class="success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <!-- MEMBERSHIP REQUESTS SECTION -->
    <h3>📋 Pending Membership Requests</h3>

    <?php if ($pending_members_result->num_rows === 0): ?>
        <p class="no-requests">No pending membership requests.</p>
    <?php else: ?>
        <?php while ($member = $pending_members_result->fetch_assoc()): ?>
            <div class="request-card">
                <p><b>Name:</b> <?= htmlspecialchars($member['name']) ?></p>
                <p><b>Email:</b> <?= htmlspecialchars($member['email']) ?></p>
                <p><b>Club:</b> <?= htmlspecialchars($member['club_name']) ?></p>
                <p><b>Requested:</b> <?= $member['joined_at'] ?></p>

                <div class="button-group">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="request_id" value="<?= $member['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-approve">Approve</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="request_id" value="<?= $member['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-reject">Reject</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <!-- EVENT REGISTRATION REQUESTS SECTION -->
    <h3>🎯 Pending Event Registration Requests</h3>

    <?php if ($pending_events_result->num_rows === 0): ?>
        <p class="no-requests">No pending event registration requests.</p>
    <?php else: ?>
        <?php while ($event_reg = $pending_events_result->fetch_assoc()): ?>
            <div class="request-card">
                <p><b>Member:</b> <?= htmlspecialchars($event_reg['name']) ?></p>
                <p><b>Email:</b> <?= htmlspecialchars($event_reg['email']) ?></p>
                <p><b>Event:</b> <?= htmlspecialchars($event_reg['title']) ?></p>
                <p><b>Club:</b> <?= htmlspecialchars($event_reg['club_name']) ?></p>
                <p><b>Requested:</b> <?= $event_reg['registered_at'] ?></p>

                <div class="button-group">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="event_request_id" value="<?= $event_reg['id'] ?>">
                        <input type="hidden" name="action_event" value="approve">
                        <button type="submit" class="btn btn-approve">Approve</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="event_request_id" value="<?= $event_reg['id'] ?>">
                        <input type="hidden" name="action_event" value="reject">
                        <button type="submit" class="btn btn-reject">Reject</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

</div>

</body>
</html>
