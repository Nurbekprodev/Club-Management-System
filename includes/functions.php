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
