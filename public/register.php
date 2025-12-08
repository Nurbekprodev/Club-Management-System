<?php
session_start();
include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

$name = $email = $password = $confirm_password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = "member";

    $name_error = validateName($name);
    $email_error = validateEmail($email);
    $password_error = validatePassword($password);

    $confirm_error = "";
    if ($password !== $confirm_password) {
        $confirm_error = "Passwords do not match.";
    }

    if (empty($name_error) && empty($email_error) && empty($password_error) && empty($confirm_error)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $check_stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            setError("This email is already registered.");
        } else {
            $insert_stmt = $connection->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
            if ($insert_stmt->execute()) {
                setSuccess("Registration successful! You can now log in.");
                $name = $email = $password = $confirm_password = "";
            } else {
                setError("Registration failed. Please try again.");
            }
        }
    } else {
        if (!empty($name_error)) setError($name_error);
        else if (!empty($email_error)) setError($email_error);
        else if (!empty($password_error)) setError($password_error);
        else if (!empty($confirm_error)) setError($confirm_error);
    }
}
?>

<main>
    <div class="container mt-4">
        <div style="max-width: 400px; margin: 0 auto;">
            <div class="card">
                <div class="card-header mb-3">Create Account</div>
                <div class="card-body">
                    <?php displayMessages(); ?>

                    <form action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="form-group">
                            <label for="name" style="display:block; margin-bottom: 6px; font-weight: 600;">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email" style="display:block; margin-bottom: 6px; font-weight: 600;">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="password" style="display:block; margin-bottom: 6px; font-weight: 600;">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" style="display:block; margin-bottom: 6px; font-weight: 600;">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 12px;">Register</button>
                    </form>

                    <p class="text-center mt-3">Already have an account? <a href="login.php" style="font-weight: 600;">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
