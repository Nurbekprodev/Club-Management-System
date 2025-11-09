<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include "../includes/database.php";

if (!isset($_GET['id'])) {
    header("Location: manage_events.php");
    exit();
}

$event_id = $_GET['id'];
$user_id = $_SESSION['user_id'];


// Fetch event data
$query = "SELECT * FROM events WHERE id = '$event_id' AND created_by = '$user_id'";
$result= mysqli_query($connection, $query);
$event = mysqli_fetch_assoc($result);

if(!$event){
    echo "Events not found or access denied";
    exit();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $date = mysqli_real_escape_string($connection, $_POST['date']);

    $update =  "UPDATE events
                SET title = '$title', description = '$description', date = '$date'
                WHERE id = '$event_id' AND created_by = '$user_id'";
            
    if(mysqli_query($connection, $update)){
        header("Location: manage_events.php?success=Event updated successfully");
        exit();
    }else{
        $error_message = "Error updating event.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>

     <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f9f9f9;
        }
        form {
            background: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            width: 400px;
        }
        input, textarea {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
        }
        button {
            padding: 8px 16px;
            cursor: pointer;
        }
        .message {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    
<h2>Edit Event</h2>

<?php if (isset($error_message)): ?>
    <p class="error"><?= $error_message ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Title:</label>
    <input type="text" name="title" value="<?= htmlspecialchars($event['title']); ?>" required>

    <label>Description:</label>
    <textarea name="description" rows="4" required><?= htmlspecialchars($event['description']); ?></textarea>

    <label>Date:</label>
    <input type="date" name="date" value="<?= $event['date']; ?>" required>

    <button type="submit" name="update_event">Update Event</button>
</form>
</body>
</html>