<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: ../public/login.php");
    exit();
}

include "../includes/database.php";

// Fetch all clubs that the member is NOT part of yet
$member_id = $_SESSION['user_id'];

// Clubs already joined
$joined_sql = "SELECT club_id FROM club_members WHERE user_id = ?";
$stmt = $connection->prepare($joined_sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$joined_result = $stmt->get_result();

$joined_clubs = [];
while ($row = $joined_result->fetch_assoc()) {
    $joined_clubs[] = $row['club_id'];
}

// Clubs already requested
$requested_sql = "SELECT club_id FROM memberships WHERE user_id = ? AND status = 'pending'";
$stmt = $connection->prepare($requested_sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$requested_result = $stmt->get_result();

$requested_clubs = [];
while ($row = $requested_result->fetch_assoc()) {
    $requested_clubs[] = $row['club_id'];
}

// Fetch all available clubs
$all_clubs_sql = "SELECT id, name FROM clubs ORDER BY name ASC";
$all_clubs_result = mysqli_query($connection, $all_clubs_sql);

// Handle request submission
$success_message = $error_message = "";
if (isset($_POST['request_club'])) {
    $club_id = $_POST['club_id'];

    // Check if already joined or requested
    if (in_array($club_id, $joined_clubs)) {
        $error_message = "You are already a member of this club.";
    } elseif (in_array($club_id, $requested_clubs)) {
        $error_message = "You have already requested membership for this club.";
    } else {
        $insert_sql = "INSERT INTO memberships (user_id, club_id, status, requested_at) VALUES (?, ?, 'pending', NOW())";
        $stmt = $connection->prepare($insert_sql);
        $stmt->bind_param("ii", $member_id, $club_id);
        if ($stmt->execute()) {
            $success_message = "Membership request sent successfully!";
        } else {
            $error_message = "Error sending request. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Club Membership</title>
    <style>
        body { font-family: Arial; margin: 40px; background: #f7f7f7; }
        h2 { color: #333; }
        .message { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        form { background: #fff; padding: 15px; border: 1px solid #ccc; width: 400px; }
        select, button { width: 100%; margin-bottom: 10px; padding: 8px; }
        a { display: inline-block; margin-top: 15px; }
    </style>
</head>
<body>

<h2>Request Membership</h2>

<?php if ($success_message): ?>
    <div class="message success"><?= $success_message ?></div>
<?php elseif ($error_message): ?>
    <div class="message error"><?= $error_message ?></div>
<?php endif; ?>

<form method="POST" action="">
    <label>Select a Club to Join:</label>
    <select name="club_id" required>
        <option value="">-- Select Club --</option>
        <?php while ($club = mysqli_fetch_assoc($all_clubs_result)) : ?>
            <?php 
            // Skip clubs already joined or requested
            if (in_array($club['id'], $joined_clubs) || in_array($club['id'], $requested_clubs)) continue; 
            ?>
            <option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['name']) ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit" name="request_club">Request Membership</button>
</form>

<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>
