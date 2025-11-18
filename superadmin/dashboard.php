<?php
session_start();
include '../includes/database.php';
include '../includes/functions.php';
include '../includes/header.php';

redirectIfNotSuperadmin();

// Fetch basic counts using prepared statements
$total_users = countRows($connection, "users");
$total_clubs = countRows($connection, "clubs");
$pending_requests = countRows($connection, "role_requests", "status = 'pending'");
?>

<main>
    <div class="container mt-4">
        <h2>Dashboard</h2>
        <p class="text-muted mb-4">System overview and key metrics</p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="card">
                <div class="card-header">Total Users</div>
                <div class="card-body" style="text-align: center;">
                    <p style="font-size: 28px; font-weight: 700; color: var(--color-primary);"><?= $total_users ?></p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Total Clubs</div>
                <div class="card-body" style="text-align: center;">
                    <p style="font-size: 28px; font-weight: 700; color: var(--color-primary);"><?= $total_clubs ?></p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Pending Role Requests</div>
                <div class="card-body" style="text-align: center;">
                    <p style="font-size: 28px; font-weight: 700; color: var(--color-primary);"><?= $pending_requests ?></p>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div class="card">
                <div class="card-header">Manage Clubs</div>
                <div class="card-body">
                    <p class="text-muted">View, edit, and delete all clubs in the system.</p>
                    <a href="manage_clubs.php" class="btn btn-primary" style="display: block; text-align: center;">Go to Clubs</a>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Manage Users</div>
                <div class="card-body">
                    <p class="text-muted">Manage user roles and permissions.</p>
                    <a href="manage_users.php" class="btn btn-primary" style="display: block; text-align: center;">Go to Users</a>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Role Requests</div>
                <div class="card-body">
                    <p class="text-muted">Review and approve pending role requests.</p>
                    <a href="role_requests.php" class="btn btn-primary" style="display: block; text-align: center;">View Requests</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
