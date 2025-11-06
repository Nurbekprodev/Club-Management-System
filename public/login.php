<?php
session_start();
include '../includes/database.php';
include '../includes/functions.php';

$email = $password = "";
$errors = [
    "email" => "",
    "password" => ""
];
$success = "";

// check form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // validate fields
    $errors["email"] = validateEmail($email);
    $errors["password"] = validatePassword($password);

    // if no errors
    if (!array_filter($errors)) {
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($connection, $sql);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password'])) {
                // start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                // if club admin, fetch their clubs
                if ($user['role'] == 'clubadmin') {
                    $user_id = $user['id'];
                    $club_query = "SELECT id, name FROM clubs WHERE created_by = '$user_id'";
                    $club_result = mysqli_query($connection, $club_query);

                    $clubs = [];
                    while ($row = mysqli_fetch_assoc($club_result)) {
                        $clubs[] = $row; // store id and name
                    }
                    $_SESSION['clubs'] = $clubs;
                }

                // redirect based on role
                if ($user['role'] == 'superadmin') {
                    header("Location: ../superadmin/dashboard.php");
                } elseif ($user['role'] == 'clubadmin') {
                    header("Location: ../clubadmin/dashboard.php");
                } else {
                    header("Location: ../member/dashboard.php");
                }
                exit();
            } else {
                $errors["password"] = "Incorrect password.";
            }
        } else {
            $errors["email"] = "Email not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Club Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Login</h2>

    <form action="" method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>"><br>
        <small style="color:red;"><?php echo $errors["email"]; ?></small><br><br>

        <label>Password:</label><br>
        <input type="password" name="password"><br>
        <small style="color:red;"><?php echo $errors["password"]; ?></small><br><br>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register</a></p>
</body>
</html>
