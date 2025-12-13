<?php
$is_logged_in = isset($_SESSION['role']) && isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['role'] : null;
$user_name = $is_logged_in ? htmlspecialchars($_SESSION['user_name']) : null;

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'ClubWeave'; ?></title>
    <link rel="stylesheet" href="<?php echo isset($css_path) ? $css_path : '../assets/css/styles.css'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

<header class="main-header">
    <div class="header-container">

        <!-- LOGO -->
        <div class="logo">
            <a href="<?php echo $is_logged_in ? '../member/dashboard.php' : '../public/index.php'; ?>">
                <span>ClubWeave</span>
            </a>
        </div>

        <!-- NAVIGATION -->
        <nav class="main-nav">
            <ul class="nav-list">

                <!-- PUBLIC ALWAYS VISIBLE -->
                <li><a href="../public/index.php" class="nav-link">Home</a></li>
                <li><a href="../public/about.php" class="nav-link">About</a></li>

                <!-- PUBLIC BROWSE LINKS (ONLY IF NOT LOGGED IN) -->
                <?php if (!$is_logged_in): ?>
                    <li><a href="../member/view_clubs.php" class="nav-link">Browse Clubs</a></li>
                    <li><a href="../member/view_events.php" class="nav-link">Browse Events</a></li>
                <?php endif; ?>

                <!-- ROLE-BASED MENUS -->
                <?php if ($is_logged_in): ?>

                    <?php if ($user_role === 'member'): ?>
                        <!-- Members only get dashboard + profile in header -->
                        <li><a href="../member/dashboard.php" class="nav-link">Dashboard</a></li>
                        <li><a href="../member/member_profile.php" class="nav-link">Profile</a></li>

                    <?php elseif ($user_role === 'clubadmin'): ?>
                        <li><a href="../clubadmin/dashboard.php" class="nav-link">Dashboard</a></li>
                        <li><a href="../clubadmin/manage_clubs.php" class="nav-link">My Clubs</a></li>
                        <li><a href="../clubadmin/manage_events.php" class="nav-link">My Events</a></li>
                        <li><a href="../clubadmin/manage_requests.php" class="nav-link">Requests</a></li>
                        <li><a href="../clubadmin/admin_profile.php" class="nav-link">Profile</a></li>

                    <?php elseif ($user_role === 'superadmin'): ?>
                        <li><a href="../superadmin/dashboard.php" class="nav-link">Dashboard</a></li>
                        <li><a href="../superadmin/manage_clubs.php" class="nav-link">Clubs</a></li>
                        <li><a href="../superadmin/manage_users.php" class="nav-link">Users</a></li>
                        <li><a href="../superadmin/role_requests.php" class="nav-link">Role Requests</a></li>
                    <?php endif; ?>

                <?php endif; ?>

            </ul>
        </nav>

        <!-- USER MENU -->
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

        <button class="btn btn-ghost" id="theme-toggle" onclick="toggleTheme()">
            <i class="fa-solid fa-moon"></i>
        </button>
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">☰</button>

    </div>
</header>
