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

