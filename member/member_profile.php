<?php
session_start();
include '../includes/functions.php';
include '../includes/header.php';
include '../includes/database.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Fetch profile
$stmt = $connection->prepare("SELECT * FROM member_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// If no profile exists, go to create
if (!$profile) {
    header("Location: create_profile.php");
    exit();
}

$profile_picture_path = !empty($profile['profile_picture'])
    ? '../uploads/profile_pictures/' . $profile['profile_picture']
    : '../uploads/profile_pictures/default_user.jpg';


// Handle profile picture display
$uploadDir = "../uploads/profile_pictures/";
$profile_picture = $profile['profile_picture']; // current image

// If user uploaded a new picture
if (!empty($_FILES['profile_picture']['name'])) {

    $newImage = uploadAndResizeImage($_FILES['profile_picture'], $uploadDir);

    if ($newImage) {
        $profile_picture = $newImage; // use new image
    }
}

?>

<main>
<div class="container mt-4">
    <div style="max-width: 600px; margin: 0 auto;">

        <a href="dashboard.php" class="btn btn-ghost mb-3">← Back</a>

        <div class="card">
            <div class="card-header">My Profile</div>
            
            <!-- <?php displayMessages() ?> -->
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

                <div class="mb-3">
                    <strong>Department:</strong>
                    <p class="text-muted"><?= sanitizeInput($profile['department']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Year of Study:</strong>
                    <p class="text-muted"><?= sanitizeInput($profile['year_of_study']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Phone:</strong>
                    <p class="text-muted"><?= sanitizeInput($profile['phone']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Date of Birth:</strong>
                    <p class="text-muted"><?= sanitizeInput($profile['dob']) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Address:</strong>
                    <p class="text-muted"><?= sanitizeInput($profile['address']) ?></p>
                </div>

                <?php if (!empty($profile['linkedin'])): ?>
                <div class="mb-3">
                    <strong>LinkedIn:</strong>
                <p class="text-muted profile-link">
                    <a href="<?= sanitizeInput($profile['linkedin']) ?>" target="_blank">
                        <?= sanitizeInput($profile['linkedin']) ?>
                    </a>
                </p>
                </div>
                <?php endif; ?>

                <?php if (!empty($profile['instagram'])): ?>
                <div class="text-muted profile-link">
                    <strong>Instagram:</strong>
                    <p class="text-muted">
                        <a href="<?= sanitizeInput($profile['instagram']) ?>" target="_blank">
                            <?= sanitizeInput($profile['instagram']) ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>

                <?php if (!empty($profile['skills'])): ?>
                <div class="mb-3">
                    <strong>Skills:</strong>
                    <p class="text-muted"><?= sanitizeInput($profile['skills']) ?></p>
                </div>
                <?php endif; ?>

                <div class="profile-info">
                    <strong>Bio:</strong>
                    <p class="text-muted bio-text"><?= nl2br(sanitizeInput($profile['bio'])) ?></p>
                </div>


                <!-- Action Buttons -->
                <div class="profile-actions" style="margin-top: 20px;">
                    <a href="edit_profile.php" class="table-action-btn edit" >Edit Profile</a>
                </div>

            </div>
        </div>

    </div>
</div>
</main>

<?php include '../includes/footer.php'; ?>
