<?php
// Validate name
function validateName($name) {
    if (empty($name)) {
        return "Please enter your full name.";
    }
    return "";
}

// Validate email
function validateEmail($email) {
    if (empty($email)) {
        return "Please enter your email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Please enter a valid email address.";
    }
    return "";
}

// Validate password
function validatePassword($password) {
    if (empty($password)) {
        return "Please enter a password.";
    } elseif (strlen($password) < 6) {
        return "Password must be at least 6 characters.";
    }
    return "";
}

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ========================================
// AUTHORIZATION & ROLE CHECKS
// ========================================

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Check if user has a specific role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Check if user is admin (clubadmin or superadmin)
function isAdmin() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['clubadmin', 'superadmin']);
}

// Check if user is a member
function isMember() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'member';
}

// ========================================
// DATABASE HELPERS
// ========================================

// Count rows in a table with optional WHERE clause
function countRows($connection, $table, $where = "") {
    $sql = "SELECT COUNT(*) AS total FROM $table";
    if (!empty($where)) {
        $sql .= " WHERE $where";
    }
    $stmt = $connection->prepare($sql);
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] ?? 0;
    }
    return 0;
}

// Fetch a single row by ID
function fetchById($connection, $table, $id) {
    $stmt = $connection->prepare("SELECT * FROM $table WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    return null;
}

// Fetch all rows from a table
function fetchAll($connection, $table, $orderBy = "id DESC") {
    $sql = "SELECT * FROM $table ORDER BY $orderBy";
    $stmt = $connection->prepare($sql);
    if ($stmt) {
        $stmt->execute();
        return $stmt->get_result();
    }
    return null;
}

// Delete a row by ID
function deleteById($connection, $table, $id) {
    $stmt = $connection->prepare("DELETE FROM $table WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    return false;
}

// ========================================
// VALIDATION HELPERS
// ========================================

// Validate positive integer
function isValidId($id) {
    return is_numeric($id) && intval($id) > 0;
}

// Validate string is not empty
function isNotEmpty($string) {
    return !empty(trim($string));
}

// Sanitize string input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// ========================================
// RESPONSE & MESSAGE HELPERS
// ========================================

// Set success message in session
function setSuccess($message) {
    $_SESSION['success_message'] = $message;
}

// Set error message in session
function setError($message) {
    $_SESSION['error_message'] = $message;
}

// Get and clear success message
function getSuccess() {
    $msg = $_SESSION['success_message'] ?? "";
    unset($_SESSION['success_message']);
    return $msg;
}

// Get and clear error message
function getError() {
    $msg = $_SESSION['error_message'] ?? "";
    unset($_SESSION['error_message']);
    return $msg;
}

// Display success/error alert HTML
function displayMessages() {
    $success = getSuccess();
    $error = getError();
    
    if (!empty($success)) {
        echo '<div style="background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-bottom:15px;">' . htmlspecialchars($success) . '</div>';
    }
    if (!empty($error)) {
        echo '<div style="background:#f8d7da; color:#721c24; padding:10px; border-radius:5px; margin-bottom:15px;">' . htmlspecialchars($error) . '</div>';
    }
}

// ========================================
// REDIRECT HELPERS
// ========================================

// Redirect with message
function redirectWithMessage($url, $message, $isError = false) {
    if ($isError) {
        setError($message);
    } else {
        setSuccess($message);
    }
    header("Location: $url");
    exit();
}

// Redirect if not logged in
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: ../public/login.php");
        exit();
    }
}

// Redirect if not admin
function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header("Location: ../public/login.php");
        exit();
    }
}

// Redirect if not superadmin
function redirectIfNotSuperadmin() {
    if (!hasRole('superadmin')) {
        header("Location: ../public/login.php");
        exit();
    }
}

// ========================================
// FORM VALIDATION FUNCTIONS
// ========================================

// Validate club name
function validateClubName($name) {
    if (empty($name)) {
        return "Club name is required.";
    } elseif (strlen($name) < 3) {
        return "Club name must be at least 3 characters.";
    } elseif (strlen($name) > 100) {
        return "Club name must not exceed 100 characters.";
    }
    return "";
}

// Validate club category
function validateCategory($category) {
    if (empty($category)) {
        return "Category is required.";
    } elseif (strlen($category) < 2) {
        return "Category must be at least 2 characters.";
    } elseif (strlen($category) > 50) {
        return "Category must not exceed 50 characters.";
    }
    return "";
}

// Validate club description
function validateDescription($description) {
    if (empty($description)) {
        return "Description is required.";
    } elseif (strlen($description) < 10) {
        return "Description must be at least 10 characters.";
    } elseif (strlen($description) > 1000) {
        return "Description must not exceed 1000 characters.";
    }
    return "";
}

// Validate event title
function validateEventTitle($title) {
    if (empty($title)) {
        return "Event title is required.";
    } elseif (strlen($title) < 3) {
        return "Event title must be at least 3 characters.";
    } elseif (strlen($title) > 150) {
        return "Event title must not exceed 150 characters.";
    }
    return "";
}

// Validate event date (must be future date)
function validateEventDate($date) {
    if (empty($date)) {
        return "Event date is required.";
    }
    $event_date = strtotime($date);
    $today = strtotime(date('Y-m-d'));
    if ($event_date === false) {
        return "Invalid date format.";
    } elseif ($event_date < $today) {
        return "Event date must be in the future.";
    }
    return "";
}

// Validate event time
function validateEventTime($time) {
    if (empty($time)) {
        return "Event time is required.";
    }
    // Check if time is in HH:MM format
    if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
        return "Invalid time format. Use HH:MM format.";
    }
    return "";
}

// Validate venue
function validateVenue($venue) {
    if (empty($venue)) {
        return "Venue is required.";
    } elseif (strlen($venue) < 3) {
        return "Venue must be at least 3 characters.";
    } elseif (strlen($venue) > 200) {
        return "Venue must not exceed 200 characters.";
    }
    return "";
}

// Validate max participants
function validateMaxParticipants($max) {
    if (empty($max)) {
        return "Max participants is required.";
    } elseif (!is_numeric($max) || intval($max) <= 0) {
        return "Max participants must be a positive number.";
    } elseif (intval($max) > 10000) {
        return "Max participants cannot exceed 10000.";
    }
    return "";
}

// Validate registration deadline
function validateRegistrationDeadline($deadline, $eventDate = null) {
    if (empty($deadline)) {
        return "Registration deadline is required.";
    }
    $deadline_date = strtotime($deadline);
    $today = strtotime(date('Y-m-d'));
    
    if ($deadline_date === false) {
        return "Invalid deadline format.";
    } elseif ($deadline_date < $today) {
        return "Registration deadline must be in the future.";
    }
    
    if ($eventDate) {
        $event_date = strtotime($eventDate);
        if ($deadline_date >= $event_date) {
            return "Registration deadline must be before the event date.";
        }
    }
    return "";
}

// Validate file upload (image)
function validateImageUpload($file) {
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return "File upload failed.";
    }

    if ($file['size'] > $max_size) {
        return "File size must not exceed 5MB.";
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed_mimes)) {
        return "Only JPEG, PNG, GIF, and WebP images are allowed.";
    }

    return "";
}

// Validate role selection
function validateRole($role) {
    $valid_roles = ['member', 'clubadmin', 'superadmin'];
    if (empty($role) || !in_array($role, $valid_roles)) {
        return "Invalid role selected.";
    }
    return "";
}

// Validate action (approve/deny/etc)
function validateAction($action, $allowed = ['approve', 'deny']) {
    if (empty($action) || !in_array($action, $allowed)) {
        return "Invalid action.";
    }
    return "";
}

// Generic form validation wrapper
function validateForm($fields) {
    $errors = [];
    foreach ($fields as $key => $error) {
        if (!empty($error)) {
            $errors[] = $error;
        }
    }
    return $errors;
}

function uploadAndResizeImage($file, $uploadDir, $maxSizeMB = 5)
{
    // Allowed MIME types
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    // Validate file uploaded
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Check file size (MB → bytes)
    if ($file['size'] > ($maxSizeMB * 1024 * 1024)) {
        return false;
    }

    // Detect MIME type using finfo (very secure)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    // Validate MIME type
    if (!array_key_exists($mimeType, $allowedTypes)) {
        return false; // ❌ Not an allowed image type
    }

    // Unique file name
    $extension = $allowedTypes[$mimeType];
    $newName = uniqid("img_", true) . "." . $extension;

    $finalPath = $uploadDir . $newName;

    // Resize → Safe GD image processing
    resizeImage($file['tmp_name'], $finalPath, 500, 500); // MAX 500x500

    return $newName;
}


function resizeImage($srcFile, $destFile, $maxWidth, $maxHeight)
{
    list($origWidth, $origHeight, $type) = getimagesize($srcFile);

    // Maintain aspect ratio
    $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
    $newWidth  = (int)($origWidth * $ratio);
    $newHeight = (int)($origHeight * $ratio);

    $image_p = imagecreatetruecolor($newWidth, $newHeight);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($srcFile);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($srcFile);
            imagealphablending($image_p, false);
            imagesavealpha($image_p, true);
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($srcFile);
            break;
        default:
            return false;
    }

    imagecopyresampled($image_p, $image, 0, 0, 0, 0,
                       $newWidth, $newHeight,
                       $origWidth, $origHeight);

    // Save final resized image
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($image_p, $destFile, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($image_p, $destFile, 8);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($image_p, $destFile, 90);
            break;
    }

    return true;
}
