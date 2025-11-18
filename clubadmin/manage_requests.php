<?php
session_start();

// Allow only clubadmins
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

$admin_id = $_SESSION['user_id'];
$success_message = $error_message = "";

// Handle membership requests
if (isset($_POST['action']) && isset($_POST['request_id'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $action = $_POST['action'];
    $request_id = intval($_POST['request_id']);

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

// Handle event registration requests
if (isset($_POST['action_event']) && isset($_POST['event_request_id'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $action_event = $_POST['action_event'];
    $event_request_id = intval($_POST['event_request_id']);

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

// Fetch pending membership requests
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

// Fetch pending event registrations
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

<main>
<div class="container mt-4">

<div class="d-flex justify-between items-center mb-4">
  <h2>Manage Requests</h2>
  <a href="dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
</div>

<?php if (!empty($success_message)): ?>
    <div style="background:#d4edda; color:#155724; padding:12px; border-radius:6px; margin-bottom:16px;">
        <?= htmlspecialchars($success_message) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div style="background:#f8d7da; color:#721c24; padding:12px; border-radius:6px; margin-bottom:16px;">
        <?= htmlspecialchars($error_message) ?>
    </div>
<?php endif; ?>

<!-- Membership Requests Section -->
<h3 class="mt-4 mb-3">📋 Pending Membership Requests</h3>

<?php if ($pending_members_result->num_rows === 0): ?>
    <div class="card text-muted text-center">No pending membership requests.</div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; margin-bottom: 30px;">
      <?php while ($member = $pending_members_result->fetch_assoc()): ?>
        <div class="card">
            <h4 class="mb-2"><?= htmlspecialchars($member['name']) ?></h4>
            <p class="text-muted mb-2"><strong>Email:</strong> <?= htmlspecialchars($member['email']) ?></p>
            <p class="text-muted mb-2"><strong>Club:</strong> <?= htmlspecialchars($member['club_name']) ?></p>
            <p class="text-muted mb-3" style="font-size: 12px;"><strong>Requested:</strong> <?= $member['joined_at'] ?></p>

            <div style="display: flex; gap: 8px;">
                <form method="POST" style="flex: 1;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="request_id" value="<?= $member['id'] ?>">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 8px;">Approve</button>
                </form>
                <form method="POST" style="flex: 1;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="request_id" value="<?= $member['id'] ?>">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="btn" style="width: 100%; padding: 8px; background: #dc3545; color: white;">Reject</button>
                </form>
            </div>
        </div>
      <?php endwhile; ?>
    </div>
<?php endif; ?>

<!-- Event Registration Requests Section -->
<h3 class="mt-4 mb-3">🎯 Pending Event Registration Requests</h3>

<?php if ($pending_events_result->num_rows === 0): ?>
    <div class="card text-muted text-center">No pending event registration requests.</div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
      <?php while ($event_reg = $pending_events_result->fetch_assoc()): ?>
        <div class="card">
            <h4 class="mb-2"><?= htmlspecialchars($event_reg['title']) ?></h4>
            <p class="text-muted mb-2"><strong>Member:</strong> <?= htmlspecialchars($event_reg['name']) ?></p>
            <p class="text-muted mb-2"><strong>Email:</strong> <?= htmlspecialchars($event_reg['email']) ?></p>
            <p class="text-muted mb-2"><strong>Club:</strong> <?= htmlspecialchars($event_reg['club_name']) ?></p>
            <p class="text-muted mb-3" style="font-size: 12px;"><strong>Requested:</strong> <?= $event_reg['registered_at'] ?></p>

            <div style="display: flex; gap: 8px;">
                <form method="POST" style="flex: 1;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="event_request_id" value="<?= $event_reg['id'] ?>">
                    <input type="hidden" name="action_event" value="approve">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 8px;">Approve</button>
                </form>
                <form method="POST" style="flex: 1;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="event_request_id" value="<?= $event_reg['id'] ?>">
                    <input type="hidden" name="action_event" value="reject">
                    <button type="submit" class="btn" style="width: 100%; padding: 8px; background: #dc3545; color: white;">Reject</button>
                </form>
            </div>
        </div>
      <?php endwhile; ?>
    </div>
<?php endif; ?>

</div>
</main>

<?php include '../includes/footer.php'; ?>
