<?php
session_start();
include '../includes/functions.php';
include '../includes/header.php';
include '../includes/database.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Fetch admin profile
$stmt = $connection->prepare("SELECT * FROM admin_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// If no profile exists → go to create page
if (!$profile) {
    header("Location: create_profile.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name  = sanitizeInput($_POST['full_name']);
    $department = sanitizeInput($_POST['department']);
    $phone      = sanitizeInput($_POST['phone']);

    // Keep old picture by default
    $uploadDir = "../uploads/profile_pictures/";
    $profile_picture = $profile['profile_picture'];

    // Upload new image if provided
    if (!empty($_FILES['profile_picture']['name'])) {

        $newImage = uploadAndResizeImage($_FILES['profile_picture'], $uploadDir);

        if ($newImage === false) {
            setError("Invalid file. Only JPG, PNG, WEBP images up to 5MB are allowed.");
            header("Location: edit_profile.php");
            exit();
        } else {
            $profile_picture = $newImage;
        }
    }

    // Validate full name
    if (empty($full_name)) {
        setError("Full name is required.");
        header("Location: edit_profile.php");
        exit();
    }

    // Update profile
    $stmt = $connection->prepare("
        UPDATE admin_profiles
        SET full_name=?, department=?, phone=?, profile_picture=?
        WHERE user_id=?
    ");

    $stmt->bind_param(
        "ssssi",
        $full_name, $department, $phone, $profile_picture, $user_id
    );

    if ($stmt->execute()) {
        redirectWithMessage("admin_profile.php", "Profile updated successfully!");
    } else {
        setError("Failed to update profile.");
        header("Location: edit_profile.php");
        exit();
    }
}
?>

<main>
<div class="container mt-4">
    <div style="max-width: 500px; margin: 0 auto;">

        <a href="admin_profile.php" class="btn btn-ghost mb-3">← Back</a>

        <div class="card">
            <div class="card-header">Edit Admin Profile</div>
            <div class="card-body">

                <?php displayMessages(); ?>

                <form method="POST" enctype="multipart/form-data">

                    <div class="form-group">
                        <label style="margin-bottom:6px;font-weight:600;">Full Name *</label>
                        <input type="text" name="full_name" class="form-control"
                               value="<?= htmlspecialchars($profile['full_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label style="margin-bottom:6px;font-weight:600;">Department</label>
                        <input type="text" name="department" class="form-control"
                               value="<?= htmlspecialchars($profile['department']) ?>">
                    </div>

                    <div class="form-group">
                        <label style="margin-bottom:6px;font-weight:600;">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?= htmlspecialchars($profile['phone']) ?>">
                    </div>

                    <div class="form-group">
                        <label style="margin-bottom:6px;font-weight:600;">Profile Picture</label>
                        <input type="file" name="profile_picture" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:12px;">
                        Save Changes
                    </button>

                </form>

            </div>
        </div>

    </div>
</div>
</main>

<?php include '../includes/footer.php'; ?>
