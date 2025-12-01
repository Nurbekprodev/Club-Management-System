<?php
session_start();

include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

redirectIfNotAdmin();

// Initialize form values
$club_name = $_POST['club_name'] ?? '';
$description = $_POST['description'] ?? '';
$category = $_POST['category'] ?? '';
$location = $_POST['location'] ?? '';
$contact_email = $_POST['contact_email'] ?? '';
$contact_phone = $_POST['contact_phone'] ?? '';
$founded_year = $_POST['founded_year'] ?? '';

if (isset($_POST['create_club'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $club_name = sanitizeInput($club_name);
    $description = sanitizeInput($description);
    $category = sanitizeInput($category);
    $location = sanitizeInput($location);
    $contact_email = sanitizeInput($contact_email);
    $contact_phone = sanitizeInput($contact_phone);
    $founded_year = intval($founded_year ?? 0);
    $created_by = $_SESSION['user_id'];

    // Validate inputs
    $name_error = validateClubName($club_name);
    $desc_error = validateDescription($description);
    $cat_error = validateCategory($category);
    
    if (!empty($name_error) || !empty($desc_error) || !empty($cat_error)) {
        setError($name_error ?: ($desc_error ?: $cat_error));
        header("Location: create_club.php");
        exit();
    }

    // Handle file upload
$logo = null;

if (!empty($_FILES['logo']['name'])) {
    // Validate image
    $upload_error = validateImageUpload($_FILES['logo']);
    if (!empty($upload_error)) {
        setError($upload_error);
        header("Location: create_club.php");
        exit();
    }

    // Upload & resize image using your existing function
    $uploadDir = "../uploads/club_images/"; // consistent upload folder
    $newImage = uploadAndResizeImage($_FILES['logo'], $uploadDir, 180, 180); // width, height optional

    if ($newImage) {
        $logo = $newImage;
    } else {
        setError("Failed to upload or resize the logo.");
        header("Location: create_club.php");
        exit();
    }
}


    $stmt = $connection->prepare("INSERT INTO clubs 
        (name, description, category, location, contact_email, contact_phone, logo, founded_year, created_by, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->bind_param("ssssssssi", $club_name, $description, $category, $location, $contact_email, $contact_phone, $logo, $founded_year, $created_by);

    if ($stmt->execute()) {
        redirectWithMessage("manage_clubs.php", "Club created successfully!");
    } else {
        setError("Error creating club. Please try again.");
        header("Location: create_club.php");
        exit();
    }
}

?>

<main>
<div class="container mt-4">
    <div style="max-width: 500px; margin: 0 auto;">
        <a href="manage_clubs.php" class="btn btn-ghost mb-3">← Back</a>
        
        <div class="card">
            <div class="card-header">Create New Club</div>
            <div class="card-body">
                <?php displayMessages(); ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div class="form-group">
                        <label for="club_name" style="display:block; margin-bottom: 6px; font-weight: 600;">Club Name</label>
                        <input type="text" id="club_name" name="club_name" class="form-control" value="<?php echo sanitizeInput($club_name); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description" style="display:block; margin-bottom: 6px; font-weight: 600;">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required><?php echo sanitizeInput($description); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="category" style="display:block; margin-bottom: 6px; font-weight: 600;">Category</label>
                        <input type="text" id="category" name="category" class="form-control" value="<?php echo sanitizeInput($category); ?>" placeholder="e.g., Sports, Arts, Tech" required>
                    </div>

                    <div class="form-group">
                        <label for="location" style="display:block; margin-bottom: 6px; font-weight: 600;">Location</label>
                        <input type="text" id="location" name="location" class="form-control" value="<?php echo sanitizeInput($location); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_email" style="display:block; margin-bottom: 6px; font-weight: 600;">Contact Email</label>
                        <input type="email" id="contact_email" name="contact_email" class="form-control" value="<?php echo sanitizeInput($contact_email); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_phone" style="display:block; margin-bottom: 6px; font-weight: 600;">Contact Phone</label>
                        <input type="tel" id="contact_phone" name="contact_phone" class="form-control" value="<?php echo sanitizeInput($contact_phone); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="founded_year" style="display:block; margin-bottom: 6px; font-weight: 600;">Founded Year</label>
                        <input type="number" id="founded_year" name="founded_year" class="form-control" value="<?php echo sanitizeInput($founded_year); ?>" min="1900" max="<?php echo date('Y'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="logo" style="display:block; margin-bottom: 6px; font-weight: 600;">Club Logo</label>
                        <input type="file" id="logo" name="logo" class="form-control" accept="image/*">
                        <small class="text-muted">Max 5MB, PNG/JPG/GIF</small>
                    </div>

                    <button type="submit" name="create_club" class="btn btn-primary" style="width: 100%; margin-top: 12px;">Create Club</button>
                </form>
            </div>
        </div>
    </div>
</div>
</main>

<?php include '../includes/footer.php'; ?>
