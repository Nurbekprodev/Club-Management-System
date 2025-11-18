<?php
session_start();
include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

// check if user is logged in and is a member
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'member') {
    header("Location: public/login.php");
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

<main>
<div class="container mt-4">

<div class="d-flex justify-between items-center mb-4">
  <h2>Request Role Upgrade</h2>
  <a href="dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
</div>

<div class="card" style="max-width: 500px; margin: 0 auto;">
    <?php if ($success): ?>
        <div style="background:#d4edda; color:#155724; padding:12px; border-radius:6px; margin-bottom:16px;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background:#f8d7da; color:#721c24; padding:12px; border-radius:6px; margin-bottom:16px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label><strong>Select Role to Request:</strong></label>
            <select name="requested_role" class="form-control" required>
                <option value="">-- Choose a role --</option>
                <option value="clubadmin">Club Admin</option>
                <option value="superadmin">Super Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px;">Submit Request</button>
    </form>
</div>

</div>
</main>

<?php include '../includes/footer.php'; ?>
