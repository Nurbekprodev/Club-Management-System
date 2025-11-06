<?php
session_start();

// Allow only club admins
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

// Check if ID is given
if (!isset($_GET['id'])) {
    header("Location: manage_clubs.php");
    exit();
}

$club_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch existing club details
$query = "SELECT * FROM clubs WHERE id='$club_id' AND created_by='$user_id'";
$result = mysqli_query($connection, $query);

if (mysqli_num_rows($result) == 0) {
    die("Club not found or you don’t have permission to edit it.");
}

$club = mysqli_fetch_assoc($result);

// Update club
if (isset($_POST['update_club'])) {
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);

    $update = "UPDATE clubs SET name='$name', description='$description' WHERE id='$club_id' AND created_by='$user_id'";
    if (mysqli_query($connection, $update)) {
        header("Location: manage_clubs.php?success=updated");
        exit();
    } else {
        $error_message = "Error updating club.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Club</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f7f7f7;
        }
        form {
            background: #fff;
            padding: 15px;
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
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<h2>Edit Club</h2>

<?php if (isset($error_message)): ?>
    <div class="error"><?= $error_message ?></div>
<?php endif; ?>

<form method="POST" action="">
    <label>Club Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($club['name']) ?>" required>

    <label>Description</label>
    <textarea name="description" rows="4" required><?= htmlspecialchars($club['description']) ?></textarea>

    <button type="submit" name="update_club">Update Club</button>
</form>

</body>
</html>
