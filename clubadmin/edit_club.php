<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

if (!isset($_GET['id'])) {
    header("Location: manage_clubs.php");
    exit();
}

$club_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM clubs WHERE id='$club_id' AND created_by='$user_id'";
$result = mysqli_query($connection, $query);
$club = mysqli_fetch_assoc($result);

if (!$club) {
    echo "Club not found or access denied.";
    exit();
}

if (isset($_POST['update_club'])) {
    $club_name = $_POST['club_name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $location = $_POST['location'];
    $contact_email = $_POST['contact_email'];
    $contact_phone = $_POST['contact_phone'];
    $founded_year = $_POST['founded_year'];

    $logo = $club['logo'];
    if (!empty($_FILES['logo']['name'])) {
        $logo = '../includes/images/' . basename($_FILES['logo']['name']);
        move_uploaded_file($_FILES['logo']['tmp_name'], $logo);
    }

    $stmt = $connection->prepare("UPDATE clubs 
        SET club_name=?, description=?, category=?, location=?, contact_email=?, contact_phone=?, logo=?, founded_year=?, updated_at=NOW()
        WHERE id=?");
    $stmt->bind_param("sssssssii", $club_name, $description, $category, $location, $contact_email, $contact_phone, $logo, $founded_year, $club_id);

    if ($stmt->execute()) {
        echo "<script>alert('Club updated successfully'); window.location='manage_clubs.php';</script>";
    } else {
        echo "<script>alert('Error updating club');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Club</title>
    <style>
        body { font-family: Arial; margin: 40px; background: #f7f7f7; }
        form { background: #fff; padding: 20px; width: 400px; border: 1px solid #ccc; }
        input, textarea { width: 100%; margin-bottom: 10px; padding: 8px; }
        button { padding: 8px 16px; cursor: pointer; }
        .error { color: red; }
    </style>
</head>
<body>

<h2>Edit Club</h2>


<?php if (isset($error)): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Club Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($club['name']) ?>" required>

    <label>Description:</label>
    <textarea name="description" rows="4" required><?= htmlspecialchars($club['description']) ?></textarea>

    <label>Category:</label>
    <input type="text" name="category" placeholder="e.g., Sports, Coding, Music">

    <label>Location:</label>
    <input type="text" name="location" placeholder="Campus / City">

    <label>Contact Email:</label>
    <input type="email" name="contact_email" required>

    <label>Contact Phone:</label>
    <input type="text" name="contact_phone">

    <label>Founded Year:</label>
    <input type="number" name="founded_year" min="1900" max="2099">

    <label>Club Logo:</label>
    <input type="file" name="logo">
    
    <button type="submit">Update Club</button>
</form>
<a href="manage_clubs.php" style="text-decoration:none;">← Back to Clubs</a>
</body>
</html>
