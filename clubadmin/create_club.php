<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $user_id = $_SESSION['user_id'];

    $query = "INSERT INTO clubs (name, description, created_by) VALUES ('$name', '$description', '$user_id')";
    if (mysqli_query($connection, $query)) {
        header("Location: manage_clubs.php?success=Club created successfully");
        exit();
    } else {
        $error = "Error creating club.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Club</title>
    <style>
        body { font-family: Arial; margin: 40px; background: #f7f7f7; }
        form { background: #fff; padding: 20px; width: 400px; border: 1px solid #ccc; }
        input, textarea { width: 100%; margin-bottom: 10px; padding: 8px; }
        button { padding: 8px 16px; cursor: pointer; }
        .error { color: red; }
    </style>
</head>
<body>

<h2>Create Club</h2>
<a href="manage_clubs.php" style="text-decoration:none;">← Back to Clubs</a>

<?php if (isset($error)): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Club Name:</label>
    <input type="text" name="name" required>

    <label>Description:</label>
    <textarea name="description" rows="4" required></textarea>

    <button type="submit">Create Club</button>
</form>

</body>
</html>
