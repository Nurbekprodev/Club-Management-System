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

<main>
<div class="container mt-4">
    <div style="max-width: 500px; margin: 0 auto;">

        <a href="dashboard.php" class="btn btn-ghost mb-3">← Back</a>

        <div class="card">
            <div class="card-header">Create Your Profile</div>
            <div class="card-body">

                <!-- <?php displayMessages(); ?> -->

                <form method="POST" enctype="multipart/form-data">

                    <div class="form-group">
                        <label for="full_name" style="display:block; margin-bottom: 6px; font-weight: 600;">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="department" style="display:block; margin-bottom: 6px; font-weight: 600;">Department</label>
                        <input type="text" id="department" name="department" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="year_of_study" style="display:block; margin-bottom: 6px; font-weight: 600;">Year of Study</label>
                        <input type="text" id="year_of_study" name="year_of_study" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="phone" style="display:block; margin-bottom: 6px; font-weight: 600;">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="dob" style="display:block; margin-bottom: 6px; font-weight: 600;">Date of Birth</label>
                        <input type="date" id="dob" name="dob" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="address" style="display:block; margin-bottom: 6px; font-weight: 600;">Address</label>
                        <input type="text" id="address" name="address" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="linkedin" style="display:block; margin-bottom: 6px; font-weight: 600;">LinkedIn</label>
                        <input type="url" id="linkedin" name="linkedin" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="instagram" style="display:block; margin-bottom: 6px; font-weight: 600;">Instagram</label>
                        <input type="url" id="instagram" name="instagram" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="skills" style="display:block; margin-bottom: 6px; font-weight: 600;">Skills</label>
                        <input type="text" id="skills" name="skills" class="form-control" placeholder="Comma separated">
                    </div>

                    <div class="form-group">
                        <label for="bio" style="display:block; margin-bottom: 6px; font-weight: 600;">Bio</label>
                        <textarea id="bio" name="bio" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="profile_picture" style="display:block; margin-bottom: 6px; font-weight: 600;">Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture" class="form-control" accept="image/*">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 12px;">
                        Create Profile
                    </button>

                </form>

            </div>
        </div>

    </div>
</div>
</main>


<?php include '../includes/footer.php'; ?>
