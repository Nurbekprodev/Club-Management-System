<?php
session_start();
include "../includes/database.php";
include "../includes/header.php";

// Not logged in → redirect
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch profile data
$stmt = $connection->prepare("SELECT name, username, email, role, profile_image, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name, $username, $email, $role, $profile_image, $created_at);
$stmt->fetch();
$stmt->close();

// Default image if none
if (!$profile_image) {
    $profile_image = "assets/default-profile.png";
}
?>

<main class="container">
    <div class="page-header">
        <h1 class="page-title">My Profile</h1>
    </div>

    <div class="card" style="max-width: 600px; margin: auto;">

        <!-- Profile Header -->
        <div class="d-flex items-center" style="gap: 20px;">
            <img src="<?php echo $profile_image; ?>" 
                 alt="Profile"
                 style="width: 100px; height: 100px; border-radius: var(--radius-pill); object-fit: cover;">

            <div>
                <h2 style="margin: 0; font-size: 1.4rem;"><?php echo $full_name; ?></h2>
                <p class="text-muted">@<?php echo $username; ?></p>

                <span class="badge 
                    <?php echo ($role === 'admin' ? 'badge-success' : 'badge-primary'); ?>">
                    <?php echo ucfirst($role); ?>
                </span>
            </div>
        </div>

        <hr style="margin: 20px 0; border-color: var(--border-color);">

        <!-- Info Section -->
        <div class="mb-3">
            <strong>Email:</strong>
            <p class="text-muted"><?php echo $email; ?></p>
        </div>

        <div class="mb-3">
            <strong>Joined:</strong>
            <p class="text-muted"><?php echo date("F j, Y", strtotime($created_at)); ?></p>
        </div>

        <div class="mb-3">
            <strong>Account Type:</strong>
            <p class="text-muted"><?php echo ucfirst($role); ?></p>
        </div>

        <div class="mt-4" style="text-align: right;">
            <a href="profile_edit.php" class="btn btn-primary">Edit Profile</a>
        </div>
    </div>
</main>

<?php include "includes/footer.php"; ?>
