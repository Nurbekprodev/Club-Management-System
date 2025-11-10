<?php
session_start();
include "../includes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: ../public/login.php");
    exit();
}

$member_id = $_SESSION['user_id'];
$success_message = $error_message = "";

// Handle join request
if (isset($_POST['join_club'])) {
    $club_id = $_POST['club_id'];

    // Check if already requested or already a member
    $check_sql = "SELECT * FROM memberships WHERE user_id=? AND club_id=?";
    $stmt = $connection->prepare($check_sql);
    $stmt->bind_param("ii", $member_id, $club_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $check_member_sql = "SELECT * FROM club_members WHERE user_id=? AND club_id=?";
    $stmt2 = $connection->prepare($check_member_sql);
    $stmt2->bind_param("ii", $member_id, $club_id);
    $stmt2->execute();
    $member_result = $stmt2->get_result();

    if ($member_result->num_rows > 0) {
        $error_message = "You are already a member of this club.";
    } elseif ($result->num_rows == 0) {
        $insert_sql = "INSERT INTO memberships (club_id, user_id, status, created_at) VALUES (?, ?, 'pending', NOW())";
        $stmt = $connection->prepare($insert_sql);
        $stmt->bind_param("ii", $club_id, $member_id);
        if ($stmt->execute()) {
            $success_message = "Request sent successfully!";
        } else {
            $error_message = "Error sending request: " . $stmt->error;
        }
    } else {
        $error_message = "You have already requested to join this club.";
    }
}

// Fetch all clubs
$clubs_sql = "SELECT * FROM clubs ORDER BY name ASC";
$clubs_result = mysqli_query($connection, $clubs_sql);

// Fetch all pending or approved memberships
$membership_sql = "SELECT club_id, status FROM memberships WHERE user_id=?";
$stmt = $connection->prepare($membership_sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$membership_result = $stmt->get_result();

$memberships = [];
while ($row = $membership_result->fetch_assoc()) {
    $memberships[$row['club_id']] = $row['status'];
}

// Fetch all approved memberships from club_members
$member_sql = "SELECT club_id FROM club_members WHERE user_id=?";
$stmt = $connection->prepare($member_sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$member_result = $stmt->get_result();

$member_clubs = [];
while ($row = $member_result->fetch_assoc()) {
    $member_clubs[$row['club_id']] = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join Clubs</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        .club-card { border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; }
        .club-card button { padding: 8px 12px; cursor: pointer; }
        .success { color: green; }
        .error { color: red; }
        a.button { display: inline-block; margin-bottom: 20px; padding: 8px 12px; background: #4CAF50; color: #fff; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>

<h2>All Clubs</h2>
<a href="dashboard.php" class="button">Back to Dashboard</a>

<?php if(isset($success_message)) echo "<p class='success'>$success_message</p>"; ?>
<?php if(isset($error_message)) echo "<p class='error'>$error_message</p>"; ?>

<?php while($club = mysqli_fetch_assoc($clubs_result)) : ?>
    <div class="club-card">
        <h3><?= htmlspecialchars($club['name']) ?></h3>
        <p><?= htmlspecialchars($club['description']) ?></p>

        <?php if(isset($member_clubs[$club['id']])): ?>
            <button disabled>Member</button>
        <?php elseif(isset($memberships[$club['id']]) && $memberships[$club['id']] == 'pending'): ?>
            <button disabled>Request Pending</button>
        <?php else: ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="club_id" value="<?= $club['id'] ?>">
                <button type="submit" name="join_club">Request to Join</button>
            </form>
        <?php endif; ?>
    </div>
<?php endwhile; ?>

</body>
</html>
