document.addEventListener('DOMContentLoaded', () => {
    // GLOBAL CSRF PROTECTOR
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form && form.tagName === 'FORM' && form.method.toUpperCase() === 'POST') {
            const csrfToken = document.getElementById('csrf_token_global')?.value;
            if (csrfToken && !form.querySelector('input[name="csrf_token"]')) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'csrf_token';
                input.value = csrfToken;
                form.appendChild(input);
            }
        }
    });

    // --- MOBILE MENU RELIABILITY FIX ---
    const mobileMenuBtn = document.querySelector('[data-bs-target="#mobileMenu"]');
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('show');
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('bi-list');
                icon.classList.toggle('bi-x-lg');
            }
        });
    }

    // Library Filter Logic
    const filterBtns = document.querySelectorAll('.ajax-filter');
    if (filterBtns.length > 0) {
        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                filterBtns.forEach(b => b.className = 'ajax-filter px-5 py-2 rounded-full font-semibold text-sm transition-all duration-300 border bg-white text-brand-900 border-slate-200 hover:border-brand-900');
                btn.className = 'ajax-filter px-5 py-2 rounded-full font-semibold text-sm transition-all duration-300 border bg-brand-900 text-white border-brand-900 shadow-md';
                
                if (mobileMenu && mobileMenu.classList.contains('show')) {
                    mobileMenu.classList.remove('show');
                }

                currentPage = 1;
                fetchFilteredBooks(true);
            });
        });
    }

    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) loadMoreBtn.addEventListener('click', () => { currentPage++; fetchFilteredBooks(false); });
});

let currentPage = 1;

// --- PROFESSIONAL TOAST NOTIFICATION ---
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-4 left-1/2 -translate-x-1/2 md:top-24 md:right-6 md:left-auto md:translate-x-0 z-[99999] flex flex-col gap-3 pointer-events-none w-[90%] md:w-auto';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const colors = type === 'success' ? 'bg-emerald-600' : (type === 'error' ? 'bg-rose-600' : 'bg-brand-900');
    const icon = type === 'success' ? 'bi-check-circle-fill' : (type === 'error' ? 'bi-exclamation-octagon-fill' : 'bi-info-circle-fill');
    
    // Using standard Tailwind classes for smooth entry
    toast.className = `${colors} text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 transition-all duration-500 transform translate-y-0 opacity-100 pointer-events-auto border border-white/10 mb-2`;
    toast.style.boxShadow = '0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1)';
    toast.innerHTML = `<i class="bi ${icon} text-xl"></i> <span class="font-bold text-sm uppercase tracking-wide">${message}</span>`;
    
    container.appendChild(toast);

    // Auto-remove with fade out
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}

async function fetchFilteredBooks(reset = true) {
    const q = document.getElementById('searchInput')?.value.trim() || '';
    const category = document.querySelector('.ajax-filter.bg-brand-900')?.getAttribute('data-category') || 'All';
    const grid = document.getElementById('book-grid');
    if (!grid) return;

    if (reset) { 
        grid.innerHTML = ''; 
        document.getElementById('grid-loader')?.classList.remove('hidden'); 
    }

    try {
        const response = await fetch(`${window.location.origin}/actions.php?action=fetch_books&q=${encodeURIComponent(q)}&category=${encodeURIComponent(category)}&page=${currentPage}`);
        const data = await response.json();
        
        if (reset && (!data.books || data.books.length === 0)) {
            grid.innerHTML = '<div class="col-span-full text-center py-16 bg-white rounded-2xl border border-slate-200"><h3 class="font-extrabold text-2xl text-brand-900">No books found</h3></div>';
        } else {
            data.books.forEach(book => {
                const saveIcon = book.is_saved ? 'bi-bookmark-fill text-accent-500' : 'bi-bookmark';
                grid.insertAdjacentHTML('beforeend', `
                    <div class="flex flex-col bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl hover:-translate-y-2 transition-all duration-500 cursor-pointer group" onclick="openQuickView(this)" data-book-id="${book.id}" data-title="${book.title.replace(/"/g, '&quot;')}" data-author="${book.author.replace(/"/g, '&quot;')}" data-description="${book.description.replace(/"/g, '&quot;')}" data-cover="${book.cover}" data-category="${book.category}">
                        <div class="relative pt-[130%] bg-slate-100 overflow-hidden">
                            <span class="absolute top-4 right-4 z-10 bg-brand-900/80 backdrop-blur-md text-white text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wider">${book.category}</span>
                            <img src="${book.cover}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        </div>
                        <div class="p-6 flex flex-col flex-grow">
                            <h4 class="font-extrabold text-lg text-brand-900 mb-1 truncate">${book.title}</h4>
                            <p class="text-sm text-slate-500 mb-4 font-medium border-b border-slate-50 pb-3">By ${book.author}</p>
                            <div class="flex gap-2 mt-auto">
                                <button onclick="event.stopPropagation(); toggleBookmark(${book.id}, this)" class="p-3 rounded-xl border border-slate-200 text-slate-400 hover:text-brand-900 transition-all bg-white"><i class="bi ${saveIcon}"></i></button>
                                <a href="download.php?id=${book.id}" target="_blank" onclick="event.stopPropagation();" class="flex-grow flex items-center justify-center bg-brand-900 text-white rounded-xl font-bold text-xs transition-all shadow-lg shadow-brand-900/10 text-decoration-none">Access</a>
                            </div>
                        </div>
                    </div>`);
            });
        }
        document.getElementById('load-more-container')?.classList.toggle('hidden', !data.hasMore);
    } catch (e) { console.error('Fetch error:', e); }
    finally { document.getElementById('grid-loader')?.classList.add('hidden'); }
}

window.toggleBookmark = async function(bookId, btnElement) {
    const csrf = document.getElementById('csrf_token_global')?.value;
    const formData = new FormData();
    formData.append('action', 'toggle_bookmark');
    formData.append('book_id', bookId);
    formData.append('csrf_token', csrf);
    
    try {
        const response = await fetch(`${window.location.origin}/actions.php`, { method: 'POST', body: formData });
        const result = await response.json();
        const icon = btnElement.querySelector('i');
        
        if (result.status === 'added') {
            icon.className = 'bi bi-bookmark-fill text-accent-500';
            showToast('Book Saved to Your Library', 'success');
        } else if (result.status === 'removed') {
            icon.className = 'bi bi-bookmark';
            showToast('Removed from Your Library', 'info');
            
            // If the user is on the profile page, we reload after a short delay so they see the book disappear
            if (window.location.pathname.includes('profile.php')) {
                setTimeout(() => window.location.reload(), 1000);
            }
        } else {
            showToast('Unable to update bookmark', 'error');
        }
    } catch (e) { 
        console.error('Bookmark error:', e);
        // Redirect only if clearly not logged in
        window.location.href = 'login.php'; 
    }
};

window.updateReadStatus = async function(bookId, status) {
    const csrf = document.getElementById('csrf_token_global')?.value;
    const formData = new FormData();
    formData.append('action', 'update_read_status');
    formData.append('book_id', bookId);
    formData.append('status', status);
    formData.append('csrf_token', csrf);
    
    try {
        await fetch(`${window.location.origin}/actions.php`, { method: 'POST', body: formData });
        showToast('Reading Status Updated', 'success');
    } catch (e) {
        showToast('Failed to update status', 'error');
    }
};

window.openQuickView = async function(el) {
    const bookId = el.getAttribute('data-book-id');
    const title = el.getAttribute('data-title');
    const author = el.getAttribute('data-author');
    const category = el.getAttribute('data-category');
    const description = el.getAttribute('data-description');
    const cover = el.getAttribute('data-cover');

    document.getElementById('quickViewBookId').value = bookId;
    document.getElementById('quickViewTitle').textContent = title;
    document.getElementById('quickViewAuthor').textContent = author;
    document.getElementById('quickViewCategory').textContent = category;
    document.getElementById('quickViewDescription').textContent = description;
    document.getElementById('quickViewCover').src = cover;
    document.getElementById('quickViewDownloadBtn').href = 'download.php?id=' + bookId;
    
    const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
    modal.show();
};