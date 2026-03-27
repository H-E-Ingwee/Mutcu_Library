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

async function fetchFilteredBooks() {
    const q = document.getElementById('searchInput').value.trim();
    const activeFilter = document.querySelector('.ajax-filter.active');
    const category = activeFilter ? activeFilter.getAttribute('data-category') : 'All';

    const loader = document.getElementById('grid-loader');
    const grid = document.getElementById('book-grid');
    loader.classList.remove('d-none');
    grid.innerHTML = '';

    try {
        const response = await fetch(`actions.php?action=fetch_books&q=${encodeURIComponent(q)}&category=${encodeURIComponent(category)}`);
        const books = await response.json();

        books.forEach(book => {
            const cardHtml = `
                <div class="col-md-4 col-lg-3 col-sm-6">
                    <div class="card book-card" onclick="openQuickView(this)" data-book-id="${book.id}" data-title="${book.title.replace(/"/g, '&quot;')}" data-author="${book.author.replace(/"/g, '&quot;')}" data-description="${book.description.replace(/"/g, '&quot;')}" data-cover="${book.cover}" data-category="${book.category}" data-drive-link="${book.drive_link}" style="cursor: pointer;">
                        <div class="book-cover-container">
                            <span class="category-badge">${book.category}</span>
                            <img src="${book.cover}" class="book-cover" alt="${book.title}">
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="card-title fw-bold mb-1" style="font-family:var(--heading-font);color:var(--primary-color);">${book.title}</h5>
                            <p class="text-muted small mb-3 border-bottom pb-2">By ${book.author}</p>
                            <p class="card-text small flex-grow-1 text-secondary mb-4">${book.description}</p>
                            <div class="d-flex gap-2 mb-3">
                                <button onclick="event.stopPropagation(); toggleBookmark(${book.id}, this)" class="btn btn-outline-secondary w-100 rounded-pill fw-bold">
                                    <i class="bi bi-bookmark me-1"></i> Bookmark
                                </button>
                            </div>
                            <a href="download.php?id=${book.id}" target="_blank" class="btn btn-outline-primary w-100 mt-auto rounded-pill fw-bold" style="border-color: var(--primary-color); color: var(--primary-color);">
                                <i class="bi bi-cloud-arrow-down me-1"></i> Access Book
                            </a>
                        </div>
                    </div>
                </div>
            `;
            grid.insertAdjacentHTML('beforeend', cardHtml);
        });
    } catch (error) {
        console.error('Fetch error:', error);
        grid.innerHTML = '<div class="col-12 text-center py-5"><i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i><h3 class="fw-bold text-danger">Error loading books</h3><p class="text-muted">Please try again later.</p></div>';
    } finally {
        loader.classList.add('d-none');
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
    if (path === 'library.php') {
        // Prevent search form submission
        const searchForm = document.getElementById('searchForm');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                fetchFilteredBooks();
            });
        }

        // Debounced search input
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(fetchFilteredBooks, 300);
            });
        }

        // Filter buttons
        document.querySelectorAll('.ajax-filter').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.ajax-filter').forEach(b => {
                    b.classList.remove('bg-brand-900', 'text-white', 'border-brand-900', 'shadow-md');
                    b.classList.add('bg-white', 'text-brand-900', 'border-slate-200');
                });
                btn.classList.remove('bg-white', 'text-brand-900', 'border-slate-200');
                btn.classList.add('bg-brand-900', 'text-white', 'border-brand-900', 'shadow-md');
                fetchFilteredBooks();
            });
        });
    }

    if (path === 'home.php' && window.MUTCU && window.MUTCU.articles) {
        // Already rendered server-side
    }
});
