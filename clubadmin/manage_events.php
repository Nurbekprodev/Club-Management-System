<?php
session_start();

// Restrict access to club admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

// Make sure the admin has at least one club
if (!isset($_SESSION['clubs']) || count($_SESSION['clubs']) == 0) {
    echo "You are not assigned to any club yet. Please create a club first.";
    exit();
}

// Connect to database
include '../includes/database.php';

// Handle form submission for adding an event
if (isset($_POST['add_event'])) {
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $date = $_POST['date'];
    $club_id = $_POST['club_id']; // selected club from dropdown
    $created_by = $_SESSION['user_id'];

    $insert = "INSERT INTO events (club_id, title, description, date, created_by) 
               VALUES ('$club_id', '$title', '$description', '$date', '$created_by')";

    if (mysqli_query($connection, $insert)) {
        $success_message = "Event added successfully!";
    } else {
        $error_message = "Error adding event.";
    }
}

// Fetch all events for this admin
$all_events = [];
foreach ($_SESSION['clubs'] as $club) {
    $club_id = $club['id'];
    $query = "SELECT * FROM events WHERE club_id = '$club_id' ORDER BY date ASC";
    $result = mysqli_query($connection, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $row['club_name'] = $club['name']; // add club name to each event
        $all_events[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Events</title>
    <style>
        body { font-family: Arial; margin: 40px; background: #f7f7f7; }
        h2 { color: #333; }
        .message { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        form { background: #fff; padding: 15px; border: 1px solid #ccc; width: 400px; }
        input, textarea, select, button { width: 100%; margin-bottom: 10px; padding: 8px; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #eee; }
        .actions a { margin-right: 10px; }
    </style>
</head>
<body>

<h2>Manage Events</h2>

<!-- Messages -->
<?php if (isset($success_message)) : ?>
    <div class="message success"><?= $success_message ?></div>
<?php elseif (isset($error_message)) : ?>
    <div class="message error"><?= $error_message ?></div>
<?php endif; ?>

<!-- Add Event Form -->
<form method="POST" action="">
    <h3>Add New Event</h3>
    
    <!-- Club selection dropdown -->
    <select name="club_id" required>
        <option value="">Select Club</option>
        <?php foreach ($_SESSION['clubs'] as $club): ?>
            <option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="title" placeholder="Event Title" required>
    <textarea name="description" placeholder="Event Description" rows="4" required></textarea>
    <input type="date" name="date" required>
    <button type="submit" name="add_event">Add Event</button>
</form>

<!-- Events Table -->
<table>
    <tr>
        <th>ID</th>
        <th>Club</th>
        <th>Title</th>
        <th>Description</th>
        <th>Date</th>
        <th>Actions</th>
    </tr>

    <?php if (count($all_events) > 0) : ?>
        <?php foreach ($all_events as $row) : ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['club_name']) ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= $row['date'] ?></td>
                <td class="actions">
                    <a href="edit_event.php?id=<?= $row['id'] ?>">Edit</a>
                    <a href="delete_event.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this event?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr><td colspan="6">No events found.</td></tr>
    <?php endif; ?>
</table>

</body>
</html>
