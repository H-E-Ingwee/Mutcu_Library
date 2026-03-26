// app.js - Shared client logic for MUTCU E-Library

function performSearch(inputId) {
    const value = document.getElementById(inputId)?.value.trim() || '';
    const params = new URLSearchParams({ q: value, category: 'All' });
    window.location.href = 'library.php?' + params.toString();
}

function goToCategory(category) {
    const params = new URLSearchParams({ category });
    window.location.href = 'library.php?' + params.toString();
}

window.addEventListener('DOMContentLoaded', () => {
    const path = window.location.pathname.split('/').pop();
    const activeItem = document.querySelector('.navbar-nav .nav-link[href="' + path + '"]');
    if (activeItem) activeItem.classList.add('active');

    // Page-specific enhancements
    if (path === 'home.php' && window.MUTCU && window.MUTCU.articles) {
        // Already rendered server-side
    }
});
