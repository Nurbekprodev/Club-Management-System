<?php
session_start();
include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

$email = $password = "";

// check form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // validate fields
    $email_error = validateEmail($email);
    $password_error = validatePassword($password);

    // if no errors
    if (empty($email_error) && empty($password_error)) {
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
                    $club_stmt = $connection->prepare("SELECT id, name FROM clubs WHERE created_by = ?");
                    $club_stmt->bind_param("i", $user_id);
                    $club_stmt->execute();
                    $club_result = $club_stmt->get_result();

                    $clubs = [];
                    while ($row = $club_result->fetch_assoc()) {
                        $clubs[] = $row;
                    }
                    $_SESSION['clubs'] = $clubs;
                }

                // Set success message and redirect based on role
                setSuccess("Login successful!");
                if ($user['role'] == 'superadmin') {
                    header("Location: ../superadmin/dashboard.php");
                } elseif ($user['role'] == 'clubadmin') {
                    header("Location: ../clubadmin/dashboard.php");
                } else {
                    header("Location: ../member/dashboard.php");
                }
                exit();
            } else {
                setError("Incorrect password.");
            }
        } else {
            setError("Email not found.");
        }
    } else {
        if (!empty($email_error)) setError($email_error);
        else if (!empty($password_error)) setError($password_error);
    }
}
?>

<main>
    <div class="container mt-4">
        <div style="max-width: 400px; margin: 0 auto; ">
            <div class="card">
                <div class="card-header mb-3">Login</div>
                <div class="card-body">
                    <?php displayMessages(); ?>

                    <form action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-group">
                            <label for="email" style="display:block; margin-bottom: 6px; font-weight: 600;">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="password" style="display:block; margin-bottom: 6px; font-weight: 600;">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 12px;">Login</button>
                    </form>

                    <p class="text-center mt-3">Don't have an account? <a href="register.php" style="font-weight: 600;">Register</a></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
