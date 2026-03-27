document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Library Filters
    const filterBtns = document.querySelectorAll('.ajax-filter');
    if (filterBtns.length > 0) {
        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Clear active states
                filterBtns.forEach(b => {
                    b.classList.remove('bg-brand-900', 'text-white', 'border-brand-900', 'shadow-md');
                    b.classList.add('bg-white', 'text-brand-900', 'border-slate-200');
                });
                
                // Set clicked state
                btn.classList.remove('bg-white', 'text-brand-900', 'border-slate-200');
                btn.classList.add('bg-brand-900', 'text-white', 'border-brand-900', 'shadow-md');
                
                // Update URL quietly
                const category = btn.getAttribute('data-category');
                const url = new URL(window.location);
                url.searchParams.set('category', category);
                url.searchParams.delete('q'); 
                window.history.pushState({}, '', url);

                fetchFilteredBooks();
            });
        });
    }

    // 2. Search Form
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const q = document.getElementById('searchInput').value.trim();
            const url = new URL(window.location);
            url.searchParams.set('q', q);
            window.history.pushState({}, '', url);
            fetchFilteredBooks();
        });
    }
});

// Fetch books based on current parameters
async function fetchFilteredBooks() {
    const searchInput = document.getElementById('searchInput');
    const q = searchInput ? searchInput.value.trim() : '';
    const activeFilter = document.querySelector('.ajax-filter.bg-brand-900');
    const category = activeFilter ? activeFilter.getAttribute('data-category') : 'All';

    const loader = document.getElementById('grid-loader');
    const grid = document.getElementById('book-grid');
    if(loader) loader.classList.remove('hidden');
    if(grid) grid.innerHTML = '';

    try {
        const response = await fetch(`actions.php?action=fetch_books&q=${encodeURIComponent(q)}&category=${encodeURIComponent(category)}`);
        const books = await response.json();

        if (books.length === 0) {
             grid.innerHTML = `
                <div class="col-span-full text-center py-16 bg-white rounded-2xl border border-slate-200">
                    <i class="bi bi-journal-x text-slate-300 text-6xl mb-4 block"></i>
                    <h3 class="font-extrabold font-heading text-2xl text-brand-900 mb-2">No books found</h3>
                    <p class="text-slate-500">Try adjusting your search keywords or filter criteria.</p>
                </div>`;
        } else {
            books.forEach(book => {
                const cardHtml = `
                    <div class="flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer group" 
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
    } catch (error) {
        console.error('Fetch error:', error);
    } finally {
        if(loader) loader.classList.add('hidden');
    }
}

// Global quickview
window.openQuickView = function(el) {
    document.getElementById('quickViewBookId').value = el.getAttribute('data-book-id');
    document.getElementById('quickViewTitle').textContent = el.getAttribute('data-title');
    document.getElementById('quickViewAuthor').textContent = el.getAttribute('data-author');
    document.getElementById('quickViewCategory').textContent = el.getAttribute('data-category');
    document.getElementById('quickViewDescription').textContent = el.getAttribute('data-description');
    document.getElementById('quickViewCover').src = el.getAttribute('data-cover');
    document.getElementById('quickViewDownloadBtn').href = 'download.php?id=' + el.getAttribute('data-book-id');

    let modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
    modal.show();
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