// ==========================
// Main UI behavior for ClubHub
// ==========================

document.addEventListener('DOMContentLoaded', function() {

    // ===== Mobile menu toggle =====
    window.toggleMobileMenu = function() {
        const nav = document.querySelector('.main-nav');
        if (!nav) return;
        nav.classList.toggle('mobile-active');
    };

    // ===== User dropdown handling =====
    document.querySelectorAll('.user-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.nextElementSibling;
            if (!dropdown) return;
            dropdown.classList.toggle('show');
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        document.querySelectorAll('.user-dropdown').forEach(userDropdown => {
            const dropdown = userDropdown.querySelector('.dropdown-content');
            if (!dropdown) return;
            if (!userDropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
    });

    // ===== Theme handling =====
    const toggleBtn = document.getElementById('theme-toggle');

    // Initialize theme from localStorage (default: light)
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    if (toggleBtn) toggleBtn.textContent = savedTheme === 'dark' ? '☀️' : '🌙';

    // Theme toggle function
    window.toggleTheme = function() {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        if (toggleBtn) toggleBtn.textContent = next === 'dark' ? '☀️' : '🌙';
    };

});
