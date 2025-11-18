<?php
// Determine user role and login status
$is_logged_in = isset($_SESSION['role']) && isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['role'] : null;
$user_name = $is_logged_in ? htmlspecialchars($_SESSION['user_name']) : null;

// Get current page for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Club Management System'; ?></title>
    <link rel="stylesheet" href="<?php echo isset($css_path) ? $css_path : '../assets/css/styles.css'; ?>">

</head>
<body>

<header class="main-header">
    <div class="header-container">
        <!-- Logo -->
        <div class="logo">
            <a href="<?php echo $is_logged_in ? '../member/dashboard.php' : '../public/login.php'; ?>">
                 <span>ClubHub</span>
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="main-nav">
            <ul class="nav-list">
                <?php if (!$is_logged_in): ?>
                    <!-- Visitor Navigation -->
                    <li><a href="../public/login.php" class="nav-link">Home</a></li>
                    <li><a href="../member/clubs.php" class="nav-link">Browse Clubs</a></li>
                    <li><a href="../member/view_events.php" class="nav-link">Browse Events</a></li>
                    

                <?php elseif ($user_role === 'member'): ?>
                    <!-- Member Navigation -->
                    <li><a href="../member/dashboard.php" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="../member/clubs.php" class="nav-link <?php echo $current_page === 'clubs.php' ? 'active' : ''; ?>">Browse Clubs</a></li>
                    <li><a href="../member/joined_clubs.php" class="nav-link <?php echo $current_page === 'joined_clubs.php' ? 'active' : ''; ?>">My Clubs</a></li>
                    <li><a href="../member/view_events.php" class="nav-link <?php echo $current_page === 'view_events.php' ? 'active' : ''; ?>">Events</a></li>

                <?php elseif ($user_role === 'clubadmin'): ?>
                    <!-- Club Admin Navigation -->
                    <li><a href="../clubadmin/dashboard.php" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="../clubadmin/manage_clubs.php" class="nav-link <?php echo $current_page === 'manage_clubs.php' ? 'active' : ''; ?>">My Clubs</a></li>
                    <li><a href="../clubadmin/manage_events.php" class="nav-link <?php echo $current_page === 'manage_events.php' ? 'active' : ''; ?>">My Events</a></li>
                    <li><a href="../clubadmin/manage_requests.php" class="nav-link <?php echo $current_page === 'manage_requests.php' ? 'active' : ''; ?>">Requests</a></li>

                <?php elseif ($user_role === 'superadmin'): ?>
                    <!-- Superadmin Navigation -->
                    <li><a href="../superadmin/dashboard.php" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="../superadmin/manage_clubs.php" class="nav-link <?php echo $current_page === 'manage_clubs.php' ? 'active' : ''; ?>">Clubs</a></li>
                    <li><a href="../superadmin/manage_users.php" class="nav-link <?php echo $current_page === 'manage_users.php' ? 'active' : ''; ?>">Users</a></li>
                    <li><a href="../superadmin/role_requests.php" class="nav-link <?php echo $current_page === 'role_requests.php' ? 'active' : ''; ?>">Role Requests</a></li>

                <?php endif; ?>
            </ul>
        </nav>

        <!-- User Menu -->
        <div class="user-menu">
            <?php if ($is_logged_in): ?>
                <div class="user-dropdown">
                    <button class="user-btn">
                        👤 <?php echo $user_name; ?> <span class="dropdown-arrow">▼</span>
                    </button>
                    <div class="dropdown-content">
                        <span class="role-badge"><?php echo ucfirst($user_role); ?></span>
                        <a href="../public/logout.php" class="dropdown-link">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="../public/login.php" class="btn btn-login">Login</a>
                    <a href="../public/register.php" class="btn btn-register">Register</a>
                </div>
            <?php endif; ?>
        </div>

        <button class="btn btn-ghost" id="theme-toggle" onclick="toggleTheme()">🌙</button>



        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">☰</button>
    </div>
</header>
