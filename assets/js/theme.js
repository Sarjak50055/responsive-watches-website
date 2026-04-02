/* ── Theme Toggle (Dark/Light Mode) ────────────────────────────────────────── */
(function () {
    const STORAGE_KEY = 'driveTheme';
    
    // Function to apply theme correctly
    function applyTheme(theme) {
        if (theme === 'light') {
            document.body.classList.add('light-mode');
        } else {
            document.body.classList.remove('light-mode');
        }

        // Update icons if toggle button is present
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            const darkIcon = themeToggle.querySelector('.theme-icon-dark');
            const lightIcon = themeToggle.querySelector('.theme-icon-light');
            if (darkIcon) darkIcon.style.display = (theme === 'light') ? 'none' : 'block';
            if (lightIcon) lightIcon.style.display = (theme === 'light') ? 'block' : 'none';
        }
    }

    // Immediately check and apply theme to prevent flash
    let savedTheme = localStorage.getItem(STORAGE_KEY);
    if (!savedTheme) {
        savedTheme = (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) ? 'light' : 'dark';
    }
    applyTheme(savedTheme);

    // Initial listener setup + icon sync
    function initThemeToggle() {
        const themeToggle = document.getElementById('themeToggle');
        if (!themeToggle) return;

        // Sync icons again once toggle is in DOM
        applyTheme(localStorage.getItem(STORAGE_KEY) || savedTheme);

        // Remove old listener if any and add new one
        themeToggle.onclick = function(e) {
            e.stopPropagation();
            const currentTheme = document.body.classList.contains('light-mode') ? 'light' : 'dark';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            localStorage.setItem(STORAGE_KEY, newTheme);
            applyTheme(newTheme);
        };
    }

    // Run init on DOM load or immediately if already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThemeToggle);
    } else {
        initThemeToggle();
    }
})();
