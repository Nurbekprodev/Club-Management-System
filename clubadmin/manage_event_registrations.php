<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

$admin_id = $_SESSION['user_id'];

// Validate event ID
if (!isset($_GET['event']) || empty($_GET['event'])) {
    die("Invalid event ID.");
}

$event_id = intval($_GET['event']);

// Ensure event belongs to this admin
$check = $connection->prepare("SELECT title FROM events WHERE id = ? AND created_by = ?");
$check->bind_param("ii", $event_id, $admin_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    die("Unauthorized access or event not found.");
}

$event = $res->fetch_assoc();

// Handle delete (remove registration)
if (isset($_GET['remove'])) {
    $reg_id = intval($_GET['remove']);
    $del = $connection->prepare("DELETE FROM event_registrations WHERE id = ? AND event_id = ?");
    $del->bind_param("ii", $reg_id, $event_id);
    $del->execute();

    echo "<script>alert('Registration removed'); window.location='manage_event_registrations.php?event=$event_id';</script>";
    exit();
}


// $sql = "
//     SELECT er.id AS reg_id, er.registered_at,
//            u.name, u.email
//     FROM event_registrations er
//     JOIN club_members cm ON er.member_id = cm.id
//     JOIN users u ON cm.user_id = u.id
//     WHERE er.event_id = ?
//     ORDER BY er.registered_at DESC
// ";

$sql = "
    SELECT 
        er.id AS reg_id,
        er.registered_at,
        u.id AS user_id,
        u.name,
        u.email
    FROM event_registrations er
    JOIN users u ON er.member_id = u.id
    WHERE er.event_id = ?
    ORDER BY er.registered_at DESC
";


$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$registrations = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Event Registrations</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f9;
        padding: 40px;
    }

    h2 {
        margin-bottom: 10px;
    }

    .back-btn {
        padding: 8px 14px;
        background: #0066cc;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 14px;
    }

    table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0px 3px 8px rgba(0,0,0,0.1);
    }

    th {
        background: #eef2f7;
        padding: 12px;
        font-weight: bold;
        text-align: left;
    }

    td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }

    .remove-btn {
        color: #cc0000;
        text-decoration: none;
        font-weight: bold;
    }

    .remove-btn:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<h2>Registrations for: <u><?= htmlspecialchars($event['title']) ?></u></h2>

<a href="event_list.php" class="back-btn">← Back to Events</a>

<?php if ($registrations->num_rows === 0): ?>
    <p>No one has registered yet.</p>

<?php else: ?>
    <table>
        <tr>
            <th>Member Name</th>
            <th>Email</th>
            <th>Registered At</th>
            <th>Action</th>
        </tr>

        <?php while ($row = $registrations->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['registered_at']) ?></td>
            <td>
                <a href="manage_event_registrations.php?event=<?= $event_id ?>&remove=<?= $row['reg_id'] ?>"
                   class="remove-btn"
                   onclick="return confirm('Remove this registration?');">
                   Remove
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php endif; ?>

</body>
</html>
