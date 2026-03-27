document.addEventListener('DOMContentLoaded', () => {
    
    // GLOBAL CSRF FORM PROTECTOR
    // Automatically injects the CSRF token into every POST form on the website before it submits
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

    // Set up filter buttons
    const filterBtns = document.querySelectorAll('.ajax-filter');
    if (filterBtns.length > 0) {
        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                filterBtns.forEach(b => {
                    b.classList.remove('bg-brand-900', 'text-white', 'border-brand-900', 'shadow-md');
                    b.classList.add('bg-white', 'text-brand-900', 'border-slate-200');
                });
                btn.classList.remove('bg-white', 'text-brand-900', 'border-slate-200');
                btn.classList.add('bg-brand-900', 'text-white', 'border-brand-900', 'shadow-md');
                
                const category = btn.getAttribute('data-category');
                const url = new URL(window.location);
                url.searchParams.set('category', category);
                url.searchParams.delete('q'); 
                window.history.pushState({}, '', url);

                currentPage = 1;
                fetchFilteredBooks(true);
            });
        });
    }

    // Listen for Sort Dropdown changes
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            currentPage = 1;
            fetchFilteredBooks(true);
        });
    }

    // Set up search form
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const q = document.getElementById('searchInput').value.trim();
            const url = new URL(window.location);
            url.searchParams.set('q', q);
            window.history.pushState({}, '', url);
            // Fetch new search starting at page 1
            currentPage = 1;
            fetchFilteredBooks(true);
        });
    }

    // Set up Load More Button
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            currentPage++;
            fetchFilteredBooks(false); // false means append, don't clear
        });
    }
});

// State for pagination
let currentPage = 1;

// Fetch books based on current parameters
async function fetchFilteredBooks(reset = true) {
    const searchInput = document.getElementById('searchInput');
    const q = searchInput ? searchInput.value.trim() : '';
    const activeFilter = document.querySelector('.ajax-filter.bg-brand-900');
    const category = activeFilter ? activeFilter.getAttribute('data-category') : 'All';
    
    // Grab active sort parameter
    const sortSelect = document.getElementById('sortSelect');
    const sort = sortSelect ? sortSelect.value : 'newest';

    const loader = document.getElementById('grid-loader');
    const grid = document.getElementById('book-grid');
    const loadMoreContainer = document.getElementById('load-more-container');
    const loadMoreBtn = document.getElementById('load-more-btn');

    if (reset) {
        if(loader) loader.classList.remove('hidden');
        if(grid) grid.innerHTML = '';
        if(loadMoreContainer) loadMoreContainer.classList.add('hidden');
    } else {
        // Show mini loading state in the button
        if(loadMoreBtn) loadMoreBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Loading...';
    }

    try {
        // Pass the sort parameter into the API
        const response = await fetch(`actions.php?action=fetch_books&q=${encodeURIComponent(q)}&category=${encodeURIComponent(category)}&sort=${encodeURIComponent(sort)}&page=${currentPage}`);
        const data = await response.json();
        const books = data.books || [];

        if (reset && books.length === 0) {
             grid.innerHTML = `
                <div class="col-span-full text-center py-16 bg-white rounded-2xl border border-slate-200">
                    <i class="bi bi-journal-x text-slate-300 text-6xl mb-4 block"></i>
                    <h3 class="font-extrabold font-heading text-2xl text-brand-900 mb-2">No books found</h3>
                    <p class="text-slate-500">Try adjusting your search keywords or filter criteria.</p>
                </div>`;
        } else {
            books.forEach(book => {
                const cardHtml = `
                    <div class="flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer group animate-in fade-in zoom-in duration-500" 
                         onclick="openQuickView(this)" 
                         data-book-id="${book.id}" data-title="${book.title.replace(/"/g, '&quot;')}" data-author="${book.author.replace(/"/g, '&quot;')}" data-description="${book.description.replace(/"/g, '&quot;')}" data-cover="${book.cover}" data-category="${book.category}" data-drive-link="${book.drive_link}">
                        
                        <div class="relative pt-[130%] bg-slate-100 overflow-hidden">
                            <span class="absolute top-3 right-3 z-10 bg-brand-900/80 backdrop-blur-sm text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                                ${book.category}
                            </span>
                            <img src="${book.cover}" alt="Cover" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                        
                        <div class="p-5 flex flex-col flex-grow">
                            <h4 class="font-bold font-heading text-lg text-brand-900 mb-1 truncate">${book.title}</h4>
                            <p class="text-sm text-slate-500 mb-3 pb-3 border-b border-slate-100">By ${book.author}</p>
                            <p class="text-sm text-slate-600 flex-grow mb-4 line-clamp-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${book.description}</p>
                            
                            <div class="flex gap-2 mt-auto">
                                <button onclick="event.stopPropagation(); toggleBookmark(${book.id}, this)" class="p-2.5 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors bg-white">
                                    <i class="bi bi-bookmark"></i>
                                </button>
                                <a href="download.php?id=${book.id}" target="_blank" onclick="event.stopPropagation();" class="flex-grow flex items-center justify-center bg-brand-900 hover:bg-brand-800 text-white rounded-xl font-semibold text-sm transition-colors text-decoration-none">
                                    <i class="bi bi-cloud-arrow-down mr-2"></i> Access
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                grid.insertAdjacentHTML('beforeend', cardHtml);
            });
        }

        // Toggle "Load More" button visibility
        if (data.hasMore) {
            if(loadMoreContainer) loadMoreContainer.classList.remove('hidden');
            if(loadMoreBtn) loadMoreBtn.innerHTML = 'Load More Books <i class="bi bi-arrow-down-short ml-1"></i>';
        } else {
            if(loadMoreContainer) loadMoreContainer.classList.add('hidden');
        }

    } catch (error) {
        console.error('Fetch error:', error);
    } finally {
        if(loader) loader.classList.add('hidden');
    }
}

// Global quickview
window.openQuickView = async function(el) {
    const bookId = el.getAttribute('data-book-id');
    const category = el.getAttribute('data-category');
    
    document.getElementById('quickViewBookId').value = bookId;
    document.getElementById('quickViewTitle').textContent = el.getAttribute('data-title');
    document.getElementById('quickViewAuthor').textContent = el.getAttribute('data-author');
    document.getElementById('quickViewCategory').textContent = category;
    document.getElementById('quickViewDescription').textContent = el.getAttribute('data-description');
    document.getElementById('quickViewCover').src = el.getAttribute('data-cover');
    document.getElementById('quickViewDownloadBtn').href = 'download.php?id=' + bookId;

    let modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
    modal.show();

    // Fetch and render Related Books
    const relatedContainer = document.getElementById('quickViewRelatedContainer');
    const relatedGrid = document.getElementById('quickViewRelatedGrid');
    if (relatedContainer && relatedGrid) {
        relatedContainer.classList.add('hidden'); // Hide while loading
        try {
            const response = await fetch(`actions.php?action=fetch_related&id=${bookId}&category=${encodeURIComponent(category)}`);
            const relatedBooks = await response.json();
            
            if (relatedBooks.length > 0) {
                relatedGrid.innerHTML = '';
                relatedBooks.forEach(book => {
                    // Clicking a related book re-triggers the Quick View for that new book
                    relatedGrid.innerHTML += `
                        <div class="cursor-pointer group" onclick="openQuickView(this)" data-book-id="${book.id}" data-title="${book.title.replace(/"/g, '&quot;')}" data-author="${book.author.replace(/"/g, '&quot;')}" data-description="${book.description.replace(/"/g, '&quot;')}" data-cover="${book.cover}" data-category="${book.category}" data-drive-link="${book.drive_link}">
                            <div class="relative pt-[140%] overflow-hidden rounded-lg shadow-sm border border-slate-200">
                                <img src="${book.cover}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            </div>
                            <h6 class="text-[10px] font-bold text-brand-900 mt-2 truncate">${book.title}</h6>
                        </div>
                    `;
                });
                relatedContainer.classList.remove('hidden');
            }
        } catch (e) { console.error('Failed to load related books', e); }
    }
};

// Secure POST Bookmark function
window.toggleBookmark = async function(bookId, btnElement) {
    if (typeof MUTCU === 'undefined' || !MUTCU.user) {
        window.location.href = 'login.php';
        return;
    }
    try {
        const formData = new FormData();
        formData.append('action', 'toggle_bookmark');
        formData.append('book_id', bookId);
        formData.append('csrf_token', document.getElementById('csrf_token_global').value); // ADD CSRF

        const response = await fetch('actions.php', { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.status === 'error') {
            alert('Notice: ' + result.message);
            return;
        }

        const icon = btnElement.querySelector('i');
        if (result.status === 'added') {
            icon.classList.remove('bi-bookmark');
            icon.classList.add('bi-bookmark-fill', 'text-accent-500');
            alert('Saved to your library!');
        } else if(result.status === 'removed') {
            icon.classList.remove('bi-bookmark-fill', 'text-accent-500');
            icon.classList.add('bi-bookmark');
            alert('Removed from your library!');
        }
    } catch (error) {
        console.error('Bookmark toggle failed', error);
        alert('Could not update bookmark due to server configuration.');
    }
};

// Update Read Status in Profile
window.updateReadStatus = async function(bookId, status) {
    try {
        const formData = new FormData();
        formData.append('action', 'update_read_status');
        formData.append('book_id', bookId);
        formData.append('status', status);
        formData.append('csrf_token', document.getElementById('csrf_token_global').value); // ADD CSRF
        
        await fetch('actions.php', { method: 'POST', body: formData });
        // Status updates invisibly in the background
    } catch (error) {
        console.error('Failed to update status', error);
    }
};