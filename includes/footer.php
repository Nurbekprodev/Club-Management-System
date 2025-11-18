
</footer>

<footer class="main-footer">
    <div class="footer-container">
        <!-- About Section -->
        <div class="footer-section">
            <h4>About ClubHub</h4>
            <p>ClubHub is a comprehensive platform for managing clubs, events, and memberships. Connect, organize, and grow your community.</p>
            <div class="social-links">
                <a href="#" title="Facebook">f</a>
                <a href="#" title="Twitter">𝕏</a>
                <a href="#" title="Instagram">📷</a>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul class="footer-links">
                <?php if (!isset($_SESSION['role'])): ?>
                    <li><a href="../public/login.php">Login</a></li>
                    <li><a href="../public/register.php">Register</a></li>
                    <li><a href="../member/clubs.php">Browse Clubs</a></li>
                    <li><a href="../member/view_events.php">View Events</a></li>
                <?php elseif ($_SESSION['role'] === 'member'): ?>
                    <li><a href="../member/dashboard.php">My Dashboard</a></li>
                    <li><a href="../member/joined_clubs.php">My Clubs</a></li>
                    <li><a href="../member/view_events.php">Events</a></li>
                    <li><a href="../member/clubs.php">Browse Clubs</a></li>
                <?php elseif ($_SESSION['role'] === 'clubadmin'): ?>
                    <li><a href="../clubadmin/dashboard.php">Dashboard</a></li>
                    <li><a href="../clubadmin/manage_clubs.php">My Clubs</a></li>
                    <li><a href="../clubadmin/manage_events.php">My Events</a></li>
                    <li><a href="../clubadmin/manage_requests.php">Requests</a></li>
                <?php elseif ($_SESSION['role'] === 'superadmin'): ?>
                    <li><a href="../superadmin/dashboard.php">Dashboard</a></li>
                    <li><a href="../superadmin/manage_clubs.php">Manage Clubs</a></li>
                    <li><a href="../superadmin/manage_users.php">Manage Users</a></li>
                    <li><a href="../superadmin/role_requests.php">Role Requests</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Contact Section -->
        <div class="footer-section">
            <h4>Contact</h4>
            <p>
                Email: <a href="mailto:info@clubhub.com">info@clubhub.com</a><br>
                Phone: <a href="tel:+1234567890">+1 (234) 567-890</a>
            </p>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom">
        <div class="footer-bottom-container">
            <p>&copy; <?php echo date('Y'); ?> ClubHub. All rights reserved.</p>
            <div class="footer-legal">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Contact</a>
            </div>
        </div>
    </div>
</footer>

<script src="../assets/js/script.js"></script>

</body>
</html>
