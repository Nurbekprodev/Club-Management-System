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

// Default image if missing
$profile_picture = !empty($profile['profile_picture'])
    ? '../uploads/profile_pictures/' . $profile['profile_picture']
    : '../assets/default_user.png';
?>

<main class="profile-container">
    <div class="profile-card">

        <h3>My Profile</h3>

        <div class="profile-image-wrapper">
            <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile Picture" class="profile-image">
        </div>

        <h4><?= htmlspecialchars($profile['full_name']) ?></h4>

        <div class="profile-info">
            <p><strong>Department:</strong> <?= htmlspecialchars($profile['department']) ?></p>
            <p><strong>Year of Study:</strong> <?= htmlspecialchars($profile['year_of_study']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($profile['phone']) ?></p>
            <p><strong>Date of Birth:</strong> <?= htmlspecialchars($profile['dob']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($profile['address']) ?></p>
<?php if (!empty($profile['linkedin'])): ?>
    <p><strong>LinkedIn:</strong> <a href="<?= htmlspecialchars($profile['linkedin']) ?>" target="_blank"><?= htmlspecialchars($profile['linkedin']) ?></a></p>
<?php endif; ?>

<?php if (!empty($profile['instagram'])): ?>
    <p><strong>Instagram:</strong> <a href="<?= htmlspecialchars($profile['instagram']) ?>" target="_blank"><?= htmlspecialchars($profile['instagram']) ?></a></p>
<?php endif; ?>

<?php if (!empty($profile['skills'])): ?>
    <p><strong>Skills:</strong> <?= htmlspecialchars($profile['skills']) ?></p>
<?php endif; ?>

            <p><strong>Bio:</strong><br><?= nl2br(htmlspecialchars($profile['bio'])) ?></p>
        </div>

        <div class="profile-actions">
            <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
            <a href="dashboard.php" class="btn btn-primary btn-block">Back</a>
        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>
