<?php
session_start();
include '../includes/functions.php';
include '../includes/header.php';
include '../includes/database.php';

redirectIfNotLoggedIn(); 

$user_id = $_SESSION['user_id'];

// Check if admin already has a profile → redirect to edit page
$stmt = $connection->prepare("SELECT * FROM admin_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    header("Location: admin_profile.php");
    exit();
}

$error = "";
$success = "";

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name  = sanitizeInput($_POST['full_name']);
    $department = sanitizeInput($_POST['department']);
    $phone      = sanitizeInput($_POST['phone']);

    // Validate required fields
    if (empty($full_name)) {
        $error = "Full name is required.";
    } else {

        // Profile picture upload
        $uploadDir = "../uploads/profile_pictures/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $profile_picture = null;

        if (!empty($_FILES['profile_picture']['name'])) {
            $newImage = uploadAndResizeImage($_FILES['profile_picture'], $uploadDir);

            if ($newImage === false) {
                $error = "Invalid file. Only JPG, PNG, WEBP up to 5MB allowed.";
            } else {
                $profile_picture = $newImage;
            }
        }

        if (empty($error)) {
            // Insert into database
            $stmt = $connection->prepare("
                INSERT INTO admin_profiles 
                (user_id, full_name, department, phone, profile_picture) 
                VALUES (?,?,?,?,?)
            ");

            $stmt->bind_param(
                "issss",
                $user_id, 
                $full_name, 
                $department, 
                $phone, 
                $profile_picture
            );

            if ($stmt->execute()) {
                redirectWithMessage("admin_profile.php", "Profile created successfully!");
            } else {
                $error = "Failed to create profile.";
            }
        }
    }
}
?>

<div class="container mt-4">
    <h2>Create Admin Profile</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mt-3">

        <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Department</label>
            <input type="text" name="department" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Profile Picture</label>
            <input type="file" name="profile_picture" class="form-control">
            <small class="text-muted">Allowed: JPG, PNG, WEBP up to 5MB</small>
        </div>

        <button type="submit" class="btn btn-primary">Create Profile</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
