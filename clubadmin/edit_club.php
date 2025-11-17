<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_clubs.php");
    exit();
}

$club_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Fetch club (With permission check)
$stmt = $connection->prepare("SELECT * FROM clubs WHERE id = ? AND created_by = ?");
$stmt->bind_param("ii", $club_id, $user_id);
$stmt->execute();
$club = $stmt->get_result()->fetch_assoc();

if (!$club) {
    die("Club not found or unauthorized access.");
}

if (isset($_POST['update_club'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $location = $_POST['location'];
    $contact_email = $_POST['contact_email'];
    $contact_phone = $_POST['contact_phone'];
    $founded_year = $_POST['founded_year'];

    // Handle Logo Upload
    $logo_path = $club['logo'];

    if (!empty($_FILES['logo']['name'])) {
        $target_dir = "../includes/images/";
        $filename = time() . "_" . basename($_FILES["logo"]["name"]);
        $target_path = $target_dir . $filename;

        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_path)) {
            $logo_path = $target_path;
        }
    }

    $update = $connection->prepare("
        UPDATE clubs 
        SET name=?, description=?, category=?, location=?, contact_email=?, 
            contact_phone=?, logo=?, founded_year=?, updated_at=NOW()
        WHERE id=? AND created_by=?
    ");

    $update->bind_param(
        "sssssssiii",
        $name, $description, $category, $location,
        $contact_email, $contact_phone, $logo_path,
        $founded_year, $club_id, $user_id
    );

    if ($update->execute()) {
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
        body { font-family: Arial; background: #f4f6f9; padding: 40px; }
        form {
            width: 420px;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0px 3px 8px rgba(0,0,0,0.1);
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background: #0066cc;
            color: white;
            border: none;
            padding: 10px 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #555;
        }
        h2 { margin-bottom: 20px; }
    </style>
</head>
<body>

<h2>Edit Club</h2>

<form method="POST" enctype="multipart/form-data">

    <label>Club Name:</label>
    <input type="text" name="name" required value="<?= htmlspecialchars($club['name']) ?>">

    <label>Description:</label>
    <textarea name="description" rows="4" required><?= htmlspecialchars($club['description']) ?></textarea>

    <label>Category:</label>
    <input type="text" name="category" value="<?= htmlspecialchars($club['category']) ?>">

    <label>Location:</label>
    <input type="text" name="location" value="<?= htmlspecialchars($club['location']) ?>">

    <label>Contact Email:</label>
    <input type="email" name="contact_email" required value="<?= htmlspecialchars($club['contact_email']) ?>">

    <label>Contact Phone:</label>
    <input type="text" name="contact_phone" value="<?= htmlspecialchars($club['contact_phone']) ?>">

    <label>Founded Year:</label>
    <input type="number" name="founded_year" min="1900" max="2099" value="<?= htmlspecialchars($club['founded_year']) ?>">

    <label>Club Logo:</label>
    <input type="file" name="logo">

    <button type="submit" name="update_club">Update Club</button>
</form>

<a href="manage_clubs.php" class="back-link">← Back to Clubs</a>

</body>
</html>
