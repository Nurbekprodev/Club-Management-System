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
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // validate fields
    $errors["email"] = validateEmail($email);
    $errors["password"] = validatePassword($password);

    // if no errors
    if (!array_filter($errors)) {
        $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
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
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
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
