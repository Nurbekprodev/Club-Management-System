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

// If no profile exists → go to create
if (!$profile) {
    header("Location: create_profile.php");
    exit();
}

// Handle profile picture path
$profile_picture_path = !empty($profile['profile_picture'])
    ? '../uploads/profile_pictures/' . $profile['profile_picture']
    : '../uploads/profile_pictures/default_user.jpg';
?>

<main>
<div class="container mt-4">
    <div style="max-width: 600px; margin: 0 auto;">

        <a href="dashboard.php" class="btn btn-ghost mb-3">← Back</a>

        <div class="card">
            <div class="card-header">Admin Profile</div>

            <div class="card-body">

                <!-- Profile Picture -->
                <img src="<?= sanitizeInput($profile_picture_path) ?>" 
                     alt="Profile Picture" 
                     class="profile-image">

                <!-- Full Name -->
                <div class="profile-info">
                    <strong>Full Name:</strong>
                    <p class="text-muted"><?= sanitizeInput($profile['full_name']) ?></p>
                </div>

                <!-- Department -->
                <div class="mb-3">
                    <strong>Department:</strong>
                    <p class="text-muted"><?= sanitizeInput($profile['department']) ?></p>
                </div>

                <!-- Phone -->
                <div class="mb-3">
                    <strong>Phone:</strong>
                    <p class="text-muted"><?= sanitizeInput($profile['phone']) ?></p>
                </div>

                <!-- Action -->
                <div class="profile-actions" style="margin-top: 20px;">
                    <a href="edit_profile.php" class="table-action-btn edit">Edit Profile</a>
                </div>

            </div>
        </div>

    </div>
</div>
</main>

<?php include '../includes/footer.php'; ?>
