<?php
session_start();
include '../includes/functions.php';
include '../includes/header.php';
include '../includes/database.php';

redirectIfNotSuperadmin(); // Only superadmin can view other users' profiles

// Get user ID from GET
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($user_id <= 0) {
    redirectWithMessage("manage_users.php", "Invalid user ID.", true);
}

// Fetch user basic info
$stmt = $connection->prepare("SELECT id, name, email, role FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
    redirectWithMessage("manage_users.php", "User not found.", true);
}

// Determine which profile table to use
$profile_table = ($user['role'] === 'member') ? 'member_profiles' : 'admin_profiles';

// Fetch profile details
$stmt = $connection->prepare("SELECT * FROM $profile_table WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Default profile picture
$profile_picture_path = (!empty($profile['profile_picture']) && file_exists("../uploads/profile_pictures/" . $profile['profile_picture']))
    ? "../uploads/profile_pictures/" . $profile['profile_picture']
    : "../uploads/profile_pictures/default_user.jpg";
?>

<main>
<div class="container mt-4" style="max-width: 600px; margin: 0 auto;">
    <a href="manage_users.php" class="btn btn-ghost mb-3">← Back to Users</a>

    <div class="card">
        <div class="card-header"><?= htmlspecialchars($user['role']) ?> Profile</div>
        <div class="card-body">

            <!-- Profile Picture -->
            <img src="<?= htmlspecialchars($profile_picture_path) ?>" alt="Profile Picture" class="profile-image">

            <!-- Name & Email -->
            <div class="profile-info">
                <strong>Full Name:</strong>
                <p class="text-muted"><?= htmlspecialchars($profile['full_name'] ?? $user['name']) ?></p>
            </div>
            <div class="mb-3">
                <strong>Email:</strong>
                <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
            </div>

            <!-- Role-specific fields -->
            <?php if ($user['role'] === 'member'): ?>
                <div class="mb-3">
                    <strong>Department:</strong>
                    <p class="text-muted"><?= htmlspecialchars($profile['department']) ?></p>
                </div>
                <div class="mb-3">
                    <strong>Year of Study:</strong>
                    <p class="text-muted"><?= htmlspecialchars($profile['year_of_study']) ?></p>
                </div>
                <div class="mb-3">
                    <strong>Phone:</strong>
                    <p class="text-muted"><?= htmlspecialchars($profile['phone']) ?></p>
                </div>
                <div class="mb-3">
                    <strong>Date of Birth:</strong>
                    <p class="text-muted"><?= htmlspecialchars($profile['dob']) ?></p>
                </div>
                <div class="mb-3">
                    <strong>Address:</strong>
                    <p class="text-muted"><?= htmlspecialchars($profile['address']) ?></p>
                </div>
                <?php if (!empty($profile['linkedin'])): ?>
                <div class="mb-3">
                    <strong>LinkedIn:</strong>
                    <p class="text-muted"><a href="<?= htmlspecialchars($profile['linkedin']) ?>" target="_blank"><?= htmlspecialchars($profile['linkedin']) ?></a></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($profile['instagram'])): ?>
                <div class="mb-3">
                    <strong>Instagram:</strong>
                    <p class="text-muted"><a href="<?= htmlspecialchars($profile['instagram']) ?>" target="_blank"><?= htmlspecialchars($profile['instagram']) ?></a></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($profile['skills'])): ?>
                <div class="mb-3">
                    <strong>Skills:</strong>
                    <p class="text-muted"><?= htmlspecialchars($profile['skills']) ?></p>
                </div>
                <?php endif; ?>
                <div class="mb-3">
                    <strong>Bio:</strong>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($profile['bio'])) ?></p>
                </div>

            <?php else: // admin ?>
                <div class="mb-3">
                    <strong>Department:</strong>
                    <p class="text-muted"><?= htmlspecialchars($profile['department']) ?></p>
                </div>
                <div class="mb-3">
                    <strong>Phone:</strong>
                    <p class="text-muted"><?= htmlspecialchars($profile['phone']) ?></p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
</main>

<?php include '../includes/footer.php'; ?>
