</footer>

<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<footer class="main-footer">
    <div class="footer-container">

        <!-- ABOUT SECTION -->
        <div class="footer-section">
            <h4>ClubWeave</h4>
            <p>Connecting students, clubs, and communities across the university in one unified platform.</p>

            <div class="social-links">
                <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" title="Twitter"><i class="fab fa-x-twitter"></i></a>
                <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
            </div>
        </div>

        <!-- QUICK LINKS -->
        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul class="footer-links">

                <!-- PUBLIC LINKS -->
                <li><a href="../public/index.php">Home</a></li>
                <li><a href="../public/about.php">About Us</a></li>
                <li><a href="../member/view_clubs.php">Browse Clubs</a></li>
                <li><a href="../member/view_events.php">Browse Events</a></li>

                <?php if (!isset($_SESSION['role'])): ?>
                    <li><a href="../public/login.php">Login</a></li>
                    <li><a href="../public/register.php">Register</a></li>

                <?php elseif ($_SESSION['role'] === 'member'): ?>
                    <li><a href="../member/dashboard.php">My Dashboard</a></li>
                    <li><a href="../member/member_profile.php">My Profile</a></li>

                <?php elseif ($_SESSION['role'] === 'clubadmin'): ?>
                    <li><a href="../clubadmin/dashboard.php">Admin Dashboard</a></li>
                    <li><a href="../clubadmin/manage_clubs.php">My Clubs</a></li>

                <?php elseif ($_SESSION['role'] === 'superadmin'): ?>
                    <li><a href="../superadmin/dashboard.php">Superadmin Panel</a></li>
                    <li><a href="../superadmin/manage_users.php">Manage Users</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- CONTACT -->
        <div class="footer-section">
            <h4>Contact</h4>
            <p>Email: <a href="mailto:info@clubweave.com">info@clubweave.com</a></p>
            <p>Phone: <a href="tel:+1234567890">+1 (234) 567-890</a></p>
        </div>
    </div>

    <!-- BOTTOM BAR -->
    <div class="footer-bottom">
        <div class="footer-bottom-container">
            <p>&copy; <?php echo date('Y'); ?> ClubWeave. All rights reserved.</p>

            <div class="footer-legal">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="../public/contact.php">Contact</a>
            </div>
        </div>
    </div>
</footer>

<script src="../assets/js/script.js"></script>

</body>
</html>
