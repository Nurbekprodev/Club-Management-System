<?php
session_start();
include "../includes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: ../public/login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    echo "Invalid club.";
    exit();
}

$club_id = intval($_GET['id']);
$success_message = $error_message = "";

// Handle join request
if (isset($_POST['join_club'])) {
    // Check if a record exists
    $checkSql = "SELECT id, status FROM club_members WHERE user_id=? AND club_id=?";
    $stmt = $connection->prepare($checkSql);
    $stmt->bind_param("ii", $member_id, $club_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0) {
        $insertSql = "INSERT INTO club_members (club_id, user_id, status, joined_at) VALUES (?, ?, 'pending', NOW())";
        $ins = $connection->prepare($insertSql);
        $ins->bind_param("ii", $club_id, $member_id);
        if ($ins->execute()) {
            $success_message = "Membership request sent successfully.";
        } else {
            $error_message = "Error sending request.";
        }
    } else {
        $row = $res->fetch_assoc();
        if ($row['status'] === 'pending') {
            $error_message = "You already have a pending request.";
        } elseif ($row['status'] === 'approved') {
            $error_message = "You are already a member.";
        } else {
            $updateSql = "UPDATE club_members SET status='pending', joined_at=NOW() WHERE id=?";
            $upd = $connection->prepare($updateSql);
            $upd->bind_param("i", $row['id']);
            if ($upd->execute()) {
                $success_message = "Membership request re-submitted successfully.";
            } else {
                $error_message = "Error re-submitting request.";
            }
        }
    }
}

// Fetch club info
$clubSql = "SELECT * FROM clubs WHERE id = ?";
$stmt = $connection->prepare($clubSql);
$stmt->bind_param("i", $club_id);
$stmt->execute();
$club = $stmt->get_result()->fetch_assoc();

if (!$club) {
    echo "Club not found.";
    exit();
}

// Check membership status
$statusSql = "SELECT status FROM club_members WHERE user_id=? AND club_id=?";
$stmt = $connection->prepare($statusSql);
$stmt->bind_param("ii", $member_id, $club_id);
$stmt->execute();
$status = $stmt->get_result()->fetch_assoc()['status'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($club['name']) ?></title>
<style>
body { font-family: Arial; margin: 40px; background:#f7f7f7; }
.club-detail {
    background: #fff;
    padding: 20px;
    border-radius: 6px;
    max-width: 700px;
    margin: auto;
    border: 1px solid #ccc;
}
img {
    width: 120px; height: 120px;
    border-radius: 8px;
    object-fit: cover;
}
button, .button {
    padding: 8px 12px;
    background: #4CAF50;
    color: #fff;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    cursor: pointer;
}
button:hover, .button:hover { background: #45a049; }
.success { color: green; }
.error { color: red; }
</style>
</head>
<body>

<a href="clubs.php" class="button">← Back to Clubs</a>
<br><br>

<div class="club-detail">
    <?php if ($club['logo']): ?>
        <img src="<?= htmlspecialchars($club['logo']) ?>" alt="Club Logo"><br><br>
    <?php endif; ?>

    <h2><?= htmlspecialchars($club['name']) ?></h2>
    <p><strong>Category:</strong> <?= htmlspecialchars($club['category'] ?: 'N/A') ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($club['location'] ?: 'N/A') ?></p>
    <p><strong>Founded:</strong> <?= htmlspecialchars($club['founded_year'] ?: '-') ?></p>
    <p><strong>Contact Email:</strong> <?= htmlspecialchars($club['contact_email'] ?: '-') ?></p>
    <p><strong>Contact Phone:</strong> <?= htmlspecialchars($club['contact_phone'] ?: '-') ?></p>
    <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($club['description'])) ?></p>

    <?php if ($success_message): ?><p class="success"><?= htmlspecialchars($success_message) ?></p><?php endif; ?>
    <?php if ($error_message): ?><p class="error"><?= htmlspecialchars($error_message) ?></p><?php endif; ?>

    <form method="POST">
        <?php
        if ($status === 'approved') {
            echo "<button disabled>Already a Member</button>";
        } elseif ($status === 'pending') {
            echo "<button disabled>Request Pending</button>";
        } else {
            echo "<button type='submit' name='join_club'>Request to Join</button>";
        }
        ?>
    </form>
</div>

</body>
</html>
