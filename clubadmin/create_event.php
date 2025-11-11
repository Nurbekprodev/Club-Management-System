<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

$success_message = $error_message = "";

// Refresh $_SESSION['clubs'] in case new club was just added
$user_id = $_SESSION['user_id'];
$club_query = "SELECT id, name FROM clubs WHERE created_by = '$user_id'";
$club_result = mysqli_query($connection, $club_query);

$clubs = [];
while ($row = mysqli_fetch_assoc($club_result)) {
    $clubs[] = $row;
}
$_SESSION['clubs'] = $clubs;

// make sure they have at least one club
if (empty($_SESSION['clubs']) || !is_array($_SESSION['clubs'])) {
    echo "No clubs created yet. Please create a club first.";
    exit();
}

// handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $date = $_POST['date'];
    $club_id = $_POST['club_id'];
    $created_by = $_SESSION['user_id'];

    if (!empty($title) && !empty($description) && !empty($date) && !empty($club_id)) {
        $insert = "INSERT INTO events (club_id, title, description, date, created_by) 
                   VALUES ('$club_id', '$title', '$description', '$date', '$created_by')";
        if (mysqli_query($connection, $insert)) {
            header("Location: manage_events.php?success=Event added successfully");
            exit();
        } else {
            $error_message = "Error adding event. Please try again.";
        }
    } else {
        $error_message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Event</title>
    <style>
        body { font-family: Arial; margin: 40px; background: #f7f7f7; }
        h2 { color: #333; }
        form { background: #fff; padding: 20px; border: 1px solid #ccc; width: 400px; }
        input, textarea, select, button {
            width: 100%; margin-bottom: 10px; padding: 8px;
        }
        button {
            background: #4CAF50; color: white; border: none; cursor: pointer;
        }
        button:hover { background: #45a049; }
        .error { color: red; }
        .back-link { display: inline-block; margin-top: 10px; }
    </style>
</head>
<body>

<h2>Create New Event</h2>

<?php if ($error_message): ?>
    <p class="error"><?= $error_message ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Club:</label>
    <select name="club_id" required>
        <option value="">Select Club</option>
        <?php foreach ($_SESSION['clubs'] as $club): ?>
            <option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Title:</label>
    <input type="text" name="title" required>

    <label>Description:</label>
    <textarea name="description" rows="4" required></textarea>

    <label>Date:</label>
    <input type="date" name="date" required>

    <button type="submit">Add Event</button>
</form>

<a href="manage_events.php" class="back-link">← Back to Events</a>

</body>
</html>
