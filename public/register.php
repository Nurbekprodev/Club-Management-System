<?php
session_start();
include '../includes/database.php';
include '../includes/functions.php';

$success = "";
$name = $email = $password = "";
$errors = [
    "name" => "",
    "email" => "",
    "password" => ""
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = "member";

    // validate fields
    $errors["name"] = validateName($name);
    $errors["email"] = validateEmail($email);
    $errors["password"] = validatePassword($password);

    // if no errors
    if (!array_filter($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // check email using prepared statement
        $check_stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $errors["email"] = "This email is already registered.";
        } else {
            // insert user using prepared statement
            $insert_stmt = $connection->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
            if ($insert_stmt->execute()) {
                $success = "Registration successful! You can now log in.";
                $name = $email = $password = "";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Club Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Register</h2>

    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <label>Full Name:</label><br>
        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>"><br>
        <small style="color:red;"><?php echo $errors["name"]; ?></small><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>"><br>
        <small style="color:red;"><?php echo $errors["email"]; ?></small><br><br>

        <label>Password:</label><br>
        <input type="password" name="password"><br>
        <small style="color:red;"><?php echo $errors["password"]; ?></small><br><br>

        <button type="submit">Register</button>
    </form>

    <?php if ($success): ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <p>Already have an account? <a href="login.php">Login</a></p>
</body>
</html>
