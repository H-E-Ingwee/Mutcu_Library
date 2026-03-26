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

function showToast(message, type = 'success') {
    const toastEl = document.getElementById('flashToast');
    const toastBody = document.getElementById('toastMessage');
    toastBody.textContent = message;
    toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

async function toggleBookmark(bookId, buttonElement) {
    try {
        const formData = new FormData();
        formData.append('action', 'toggle_bookmark');
        formData.append('book_id', bookId);

        const response = await fetch('actions.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();
        if (result.status === 'success') {
            const icon = buttonElement.querySelector('i');
            if (result.bookmarked) {
                icon.classList.remove('bi-bookmark');
                icon.classList.add('bi-bookmark-fill');
                buttonElement.classList.remove('btn-outline-secondary');
                buttonElement.classList.add('btn-secondary');
                showToast('Book saved to your library!', 'success');
            } else {
                icon.classList.remove('bi-bookmark-fill');
                icon.classList.add('bi-bookmark');
                buttonElement.classList.remove('btn-secondary');
                buttonElement.classList.add('btn-outline-secondary');
                showToast('Book removed from your library.', 'info');
            }
        } else {
            showToast('Error updating bookmark.', 'danger');
        }
    } catch (error) {
        console.error('Bookmark error:', error);
        showToast('Error updating bookmark.', 'danger');
    }
}

function openQuickView(buttonElement) {
    const bookId = buttonElement.getAttribute('data-book-id');
    const title = buttonElement.getAttribute('data-title');
    const author = buttonElement.getAttribute('data-author');
    const description = buttonElement.getAttribute('data-description');
    const cover = buttonElement.getAttribute('data-cover');
    const category = buttonElement.getAttribute('data-category');
    const driveLink = buttonElement.getAttribute('data-drive-link');

    document.getElementById('quickViewBookId').value = bookId;
    document.getElementById('quickViewTitle').textContent = title;
    document.getElementById('quickViewAuthor').textContent = author;
    document.getElementById('quickViewDescription').textContent = description;
    document.getElementById('quickViewCover').src = cover;
    document.getElementById('quickViewCategory').textContent = category;
    document.getElementById('quickViewDownloadBtn').href = driveLink;

    // Update bookmark button state if needed (could check if bookmarked)
    const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
    modal.show();
}

window.addEventListener('DOMContentLoaded', () => {
    const path = window.location.pathname.split('/').pop();
    const activeItem = document.querySelector('.navbar-nav .nav-link[href="' + path + '"]');
    if (activeItem) activeItem.classList.add('active');

    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
            themeToggle.innerHTML = '<i class="bi bi-brightness-high"></i>';
        } else {
            document.documentElement.setAttribute('data-bs-theme', 'light');
            themeToggle.innerHTML = '<i class="bi bi-moon-stars"></i>';
        }

        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            themeToggle.innerHTML = newTheme === 'dark' ? '<i class="bi bi-brightness-high"></i>' : '<i class="bi bi-moon-stars"></i>';
        });
    }

    // Page-specific enhancements
    if (path === 'home.php' && window.MUTCU && window.MUTCU.articles) {
        // Already rendered server-side
    }
});
