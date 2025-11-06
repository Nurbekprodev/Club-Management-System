<?php
session_start();
include '../../includes/database.php';

// check if user is logged in and is a member
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'member') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $requested_role = $_POST['requested_role'];

    // check if there is already a pending request
    $check = "SELECT * FROM role_requests WHERE user_id=$user_id AND status='pending'";
    $result = mysqli_query($connection, $check);

    if (mysqli_num_rows($result) > 0) {
        $error = "You already have a pending request.";
    } else {
        $sql = "INSERT INTO role_requests (user_id, requested_role) VALUES ($user_id, '$requested_role')";
        if (mysqli_query($connection, $sql)) {
            $success = "Your role request has been submitted!";
        } else {
            $error = "Error submitting request. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Role Upgrade | Club Management</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <h2>Request Role Upgrade</h2>

    <?php if ($success): ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Select Role to Request:</label><br>
        <select name="requested_role" required>
            <option value="clubadmin">Club Admin</option>
            <option value="superadmin">Super Admin</option>
        </select><br><br>
        <button type="submit">Submit Request</button>
    </form>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
