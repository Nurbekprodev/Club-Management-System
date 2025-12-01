<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'clubadmin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

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
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $founded_year = intval($_POST['founded_year'] ?? 0);

    // Validation
    $errors = [];
    if (empty($name) || strlen($name) < 3) $errors[] = "Club name must be at least 3 characters.";
    if (empty($description) || strlen($description) < 10) $errors[] = "Description must be at least 10 characters.";
    if (empty($contact_email) || !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (!empty($contact_phone) && !preg_match('/^[0-9\-\+\(\)\s]+$/', $contact_phone)) $errors[] = "Invalid phone number.";
    if ($founded_year && ($founded_year < 1900 || $founded_year > date('Y'))) $errors[] = "Founded year must be between 1900 and current year.";

    if (!empty($errors)) {
        foreach ($errors as $error) {
            setError($error);
        }
        header("Location: edit_club.php?id=$club_id");
        exit();
    }

    // Handle Logo Upload
    $logo_path = $club['logo'];

// Handle new logo upload
if (!empty($_FILES['logo']['name'])) {
    // Validate the image (type, size)
    $upload_error = validateImageUpload($_FILES['logo']);
    if (!empty($upload_error)) {
        setError($upload_error);
        header("Location: edit_club.php?id=$club_id");
        exit();
    }

    // Generate a safe unique filename
    $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
    $safe_filename = bin2hex(random_bytes(8)) . '.' . $file_extension;

    $upload_dir = "../uploads/club_images/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $target_path = $upload_dir . $safe_filename;

    if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_path)) {
        // Delete old logo if exists
        if (!empty($club['logo']) && file_exists($club['logo'])) {
            unlink($club['logo']);
        }
        $logo_path = $target_path;
    } else {
        setError("Error uploading logo file.");
        header("Location: edit_club.php?id=$club_id");
        exit();
    }
}

// Fallback to default logo if none
$logo_path = !empty($logo_path) && file_exists($logo_path)
    ? $logo_path
    : "../uploads/club_images/default_img.jpg";


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
        redirectWithMessage("manage_clubs.php", "Club updated successfully!");
    } else {
        setError("Error updating club");
        header("Location: edit_club.php?id=$club_id");
        exit();
    }
}
?>

<main>
<div class="container mt-4">
    <div style="max-width: 500px; margin: 0 auto;">
        <a href="manage_clubs.php" class="btn btn-ghost mb-3">← Back</a>
        
        <div class="card">
            <div class="card-header">Edit Club</div>
            <div class="card-body">
                <?php displayMessages(); ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Club Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($club['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required><?= htmlspecialchars($club['description']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" class="form-control" value="<?= htmlspecialchars($club['category']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" class="form-control" value="<?= htmlspecialchars($club['location']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="contact_email">Contact Email</label>
                        <input type="email" id="contact_email" name="contact_email" class="form-control" value="<?= htmlspecialchars($club['contact_email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_phone">Contact Phone</label>
                        <input type="text" id="contact_phone" name="contact_phone" class="form-control" value="<?= htmlspecialchars($club['contact_phone']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="founded_year">Founded Year</label>
                        <input type="number" id="founded_year" name="founded_year" class="form-control" min="1900" max="<?= date('Y') ?>" value="<?= htmlspecialchars($club['founded_year']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="logo">Club Logo</label>
                        <?php if (!empty($club['logo']) && file_exists($club['logo'])): ?>
                            <div style="margin-bottom: 10px;">
                                <img src="<?= htmlspecialchars($club['logo']) ?>" alt="Club Logo" style="max-width: 150px; border-radius: 4px;">
                                <small class="text-muted d-block">Current Logo</small>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="logo" name="logo" class="form-control" accept="image/*">
                        <small class="text-muted">Leave blank to keep current logo. Max 5MB (JPEG, PNG, GIF)</small>
                    </div>

              
                    <button type="submit" name="update_club" class="btn btn-primary" style="width: 100%;">Update Club</button>
                </form>
            </div> <!-- card-body -->
        </div> <!-- card -->
    </div> <!-- container -->
</main>

<?php include '../includes/footer.php'; ?>
