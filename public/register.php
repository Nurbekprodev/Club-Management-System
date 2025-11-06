<?php
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

        // check email
        $check = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($connection, $check);

        if (mysqli_num_rows($result) > 0) {
            $errors["email"] = "This email is already registered.";
        } else {
            // insert user
            $sql = "INSERT INTO users (name, email, password, role)
                    VALUES ('$name', '$email', '$hashed_password', '$role')";
            if (mysqli_query($connection, $sql)) {
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
