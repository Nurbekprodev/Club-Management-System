<?php
session_start();
include '../includes/functions.php';
include '../includes/header.php';

redirectIfNotLoggedIn();
if ($_SESSION['role'] !== 'member') {
    header("Location: ../public/login.php");
    exit();
}
?>

<main>
    <div class="container mt-4">

        <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?>!</h2>
        <p class="text-muted mb-4">Your dashboard to manage clubs and events</p>

        <div class="dashboard-grid">

            <div class="card">
                <div class="card-header">Browse Clubs</div>
                <div class="card-body">
                    <p>Discover and join clubs that interest you.</p>
                    <a href="clubs.php" class="btn btn-primary btn-block">View Clubs</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">My Clubs</div>
                <div class="card-body">
                    <p>View clubs you've joined and your membership status.</p>
                    <a href="joined_clubs.php" class="btn btn-primary btn-block">My Clubs</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Browse Events</div>
                <div class="card-body">
                    <p>Explore and register for upcoming events.</p>
                    <a href="view_events.php" class="btn btn-primary btn-block">View Events</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Request Role</div>
                <div class="card-body">
                    <p>Request a specific role to gain more permissions.</p>
                    <a href="request_role.php" class="btn btn-primary btn-block">Request Role</a>
                </div>
            </div>

        </div>

    </div>
</main>


<?php include '../includes/footer.php'; ?>
