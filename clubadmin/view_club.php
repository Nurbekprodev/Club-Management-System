<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    die("Invalid club ID.");
}

$club_id = intval($_GET['id']);

// Ensure club belongs to this admin
$stmt = $connection->prepare("SELECT * FROM clubs WHERE id = ? AND created_by = ?");
$stmt->bind_param("ii", $club_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Club not found or unauthorized access.");
}

$club = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>View Club</title>
<style>
    body { font-family: Arial; background: #f4f6f9; padding: 40px; }
    .container {
        background: #fff; padding: 25px; width: 500px;
        margin: auto; border-radius: 8px;
        box-shadow: 0px 3px 8px rgba(0,0,0,0.1);
    }
    h2 { margin-bottom: 15px; }
    .row { margin-bottom: 10px; }
    .label { font-weight: bold; color: #333; }
    .value { color: #555; }
    img { max-width: 120px; margin-top: 10px; border-radius: 6px; }
    a.btn { display: inline-block; padding: 8px 14px; background: #0066cc;
        color: #fff; text-decoration: none; border-radius: 5px; margin-top: 15px; }
</style>
</head>

<body>

<div class="container">
    <h2>Club Details</h2>

    <div class="row"><span class="label">Name:</span> <span class="value"><?= htmlspecialchars($club['name']) ?></span></div>
    <div class="row"><span class="label">Description:</span> <span class="value"><?= htmlspecialchars($club['description']) ?></span></div>
    <div class="row"><span class="label">Category:</span> <span class="value"><?= htmlspecialchars($club['category']) ?></span></div>
    <div class="row"><span class="label">Location:</span> <span class="value"><?= htmlspecialchars($club['location']) ?></span></div>
    <div class="row"><span class="label">Email:</span> <span class="value"><?= htmlspecialchars($club['contact_email']) ?></span></div>
    <div class="row"><span class="label">Phone:</span> <span class="value"><?= htmlspecialchars($club['contact_phone']) ?></span></div>
    <div class="row"><span class="label">Founded Year:</span> <span class="value"><?= htmlspecialchars($club['founded_year']) ?></span></div>

    <?php if (!empty($club['logo'])): ?>
        <div class="row"><span class="label">Logo:</span><br>
        <img src="<?= $club['logo'] ?>" alt="Club Logo"></div>
    <?php endif; ?>

    <a href="manage_clubs.php" class="btn">← Back</a>
</div>

</body>
</html>
