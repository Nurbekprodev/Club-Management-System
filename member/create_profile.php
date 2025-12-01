<?php
session_start();
include '../includes/functions.php';
include '../includes/header.php';
include '../includes/database.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Check if profile already exists → redirect to profile
$stmt = $connection->prepare("SELECT id FROM member_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    header("Location: member_profile.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name  = sanitizeInput($_POST['full_name']);
    $department = sanitizeInput($_POST['department']);
    $year       = sanitizeInput($_POST['year_of_study']);
    $phone      = sanitizeInput($_POST['phone']);
    $dob        = sanitizeInput($_POST['dob']);
    $address    = sanitizeInput($_POST['address']);
    $linkedin   = sanitizeInput($_POST['linkedin']);
    $instagram  = sanitizeInput($_POST['instagram']);
    $skills     = sanitizeInput($_POST['skills']);
    $bio        = sanitizeInput($_POST['bio']);

    // Handle profile picture upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $profile_picture = 'profile_' . $user_id . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], '../uploads/profile_pictures/' . $profile_picture);
    }

    if (empty($full_name)) {
        setError("Full name is required.");
    } else {
        $stmt = $connection->prepare("
            INSERT INTO member_profiles 
            (user_id, full_name, department, year_of_study, phone, dob, address, linkedin, instagram, skills, bio, profile_picture) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "isssssssssss",
            $user_id, $full_name, $department, $year, $phone, $dob, $address, $linkedin, $instagram, $skills, $bio, $profile_picture
        );

        if ($stmt->execute()) {
            redirectWithMessage("member_profile.php", "Profile created successfully!");
        } else {
            setError("Something went wrong.");
        }
    }
}
?>

<main class="profile-container">
    <div class="profile-card">

        <h3>Create Your Profile</h3>

        <?php displayMessages(); ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="form-group mb-3">
                <label>Full Name *</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label>Department</label>
                <input type="text" name="department" class="form-control">
            </div>

            <div class="form-group mb-3">
                <label>Year of Study</label>
                <input type="text" name="year_of_study" class="form-control">
            </div>

            <div class="form-group mb-3">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>

            <div class="form-group mb-3">
                <label>Date of Birth</label>
                <input type="date" name="dob" class="form-control">
            </div>

            <div class="form-group mb-3">
                <label>Address</label>
                <input type="text" name="address" class="form-control">
            </div>

            <div class="form-group mb-3">
                <label>LinkedIn</label>
                <input type="url" name="linkedin" class="form-control">
            </div>

            <div class="form-group mb-3">
                <label>Instagram</label>
                <input type="url" name="instagram" class="form-control">
            </div>

            <div class="form-group mb-3">
                <label>Skills</label>
                <input type="text" name="skills" class="form-control" placeholder="Comma separated">
            </div>

            <div class="form-group mb-3">
                <label>Bio</label>
                <textarea name="bio" rows="4" class="form-control"></textarea>
            </div>

            <div class="form-group mb-3">
                <label>Profile Picture</label>
                <input type="file" name="profile_picture" class="form-control">
            </div>

            <button type="submit" class="btn btn-success">Create Profile</button>

        </form>

    </div>
</main>

<?php include '../includes/footer.php'; ?>
