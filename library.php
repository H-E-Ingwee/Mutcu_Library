<?php
require_once __DIR__ . '/functions.php';
$currentUser = currentUser();

$category = $_GET['category'] ?? 'All';
$q = trim($_GET['q'] ?? '');
$limit = 12; // Only load 12 books on initial render to keep the page fast

// Manual fast filtering for the initial render
$sql = 'SELECT * FROM books WHERE 1=1';
$params = [];
if ($category !== 'All') { $sql .= ' AND category = ?'; $params[] = $category; }
if ($q !== '') { $sql .= ' AND (title LIKE ? OR author LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }

// Count for pagination
$countStmt = $pdo->prepare(str_replace('SELECT *', 'SELECT COUNT(*)', $sql));
$countStmt->execute($params);
$totalBooks = $countStmt->fetchColumn();
$hasMore = $totalBooks > $limit;

$sql .= ' ORDER BY id DESC LIMIT ' . $limit;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
processBooks($books);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUTCU E-Library | Books</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: { fontFamily: { heading: ['Montserrat', 'sans-serif'], body: ['Lato', 'sans-serif'], },
                colors: { brand: { 900: '#0f172a', 800: '#1e293b', 50: '#f8fafc', }, accent: { 500: '#f97316', 600: '#ea580c', } } }
            }
        }
    </script>
    <style> h1, h2, h3, h4, h5, h6 { font-family: 'Montserrat', sans-serif; } </style>
</head>
<body class="font-body bg-brand-50 flex flex-col min-h-screen text-slate-800">
    <?php include __DIR__.'/partials/header.php'; ?>
    
    <main class="flex-grow py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row justify-between items-end mb-10 gap-6">
                <div class="w-full lg:w-1/2">
                    <h2 class="text-3xl font-extrabold font-heading text-brand-900 mb-2">E-Library Catalog</h2>
                    <div class="w-16 h-1 bg-accent-500 rounded-full mb-4"></div>
                    <p class="text-slate-500 text-lg">Browse or search for resources securely hosted on Google Drive.</p>
                </div>
                
                <div class="w-full lg:w-1/2">
                    <form id="searchForm" action="library.php" method="get" class="relative group shadow-sm hover:shadow-md transition-shadow rounded-full">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="bi bi-search text-slate-400"></i>
                        </div>
                        <input id="searchInput" type="text" name="q" value="<?=htmlspecialchars($q)?>" 
                               class="block w-full pl-11 pr-24 py-4 rounded-full border border-slate-200 focus:ring-2 focus:ring-accent-500 focus:border-accent-500 outline-none text-slate-700 bg-white transition-all" 
                               placeholder="Search books by title or author...">
                        <button type="submit" class="absolute inset-y-1.5 right-1.5 px-6 bg-accent-500 hover:bg-accent-600 text-white font-bold rounded-full transition-colors border-0">
                            Find
                        </button>
                    </form>
                </div>
            </div>

            <!-- Category Filters -->
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-12">
                <div class="flex flex-wrap gap-3">
                    <?php foreach (['All','Faith','Leadership','Purpose','Relationships'] as $cat): ?>
                        <button data-category="<?=urlencode($cat)?>" 
                           class="ajax-filter px-5 py-2 rounded-full font-semibold text-sm transition-all duration-300 border <?=($category === $cat ? 'bg-brand-900 text-white border-brand-900 shadow-md' : 'bg-white text-brand-900 border-slate-200 hover:border-brand-900')?>">
                           <?=htmlspecialchars($cat)?> Books
                        </button>
                    <?php endforeach; ?>
                </div>
                
                <!-- NEW: Advanced Sort Dropdown -->
                <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-full border border-slate-200 shadow-sm w-full md:w-auto">
                    <i class="bi bi-sort-down text-slate-400"></i>
                    <select id="sortSelect" class="bg-transparent border-none text-brand-900 text-sm font-bold outline-none cursor-pointer focus:ring-0 w-full">
                        <option value="newest">Newest Added</option>
                        <option value="popular">Most Popular</option>
                        <option value="az">A-Z (Title)</option>
                    </select>
                </div>
            </div>

            <div id="grid-loader" class="hidden flex justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-accent-500"></div>
            </div>
            
            <!-- Book Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8" id="book-grid">
                <?php if (!$books): ?>
                    <div class="col-span-full text-center py-16 bg-white rounded-2xl border border-slate-200">
                        <i class="bi bi-journal-x text-slate-300 text-6xl mb-4 block"></i>
                        <h3 class="font-extrabold font-heading text-2xl text-brand-900 mb-2">No books found</h3>
                        <p class="text-slate-500">Try adjusting your search keywords or filter criteria.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
                        <div class="flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer group" 
                             onclick="openQuickView(this)" 
                             data-book-id="<?=$book['id']?>" 
                             data-title="<?=htmlspecialchars($book['title'])?>" 
                             data-author="<?=htmlspecialchars($book['author'])?>" 
                             data-description="<?=htmlspecialchars($book['description'])?>" 
                             data-cover="<?=htmlspecialchars($book['cover'])?>" 
                             data-category="<?=htmlspecialchars($book['category'])?>" 
                             data-drive-link="<?=htmlspecialchars($book['drive_link'])?>">
                            
                            <div class="relative pt-[130%] bg-slate-100 overflow-hidden">
                                <span class="absolute top-3 right-3 z-10 bg-brand-900/80 backdrop-blur-sm text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                                    <?=htmlspecialchars($book['category'])?>
                                </span>
                                <img src="<?=htmlspecialchars($book['cover'])?>" alt="Cover" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>
                            
                            <div class="p-5 flex flex-col flex-grow">
                                <h4 class="font-bold font-heading text-lg text-brand-900 mb-1 truncate"><?=htmlspecialchars($book['title'])?></h4>
                                <p class="text-sm text-slate-500 mb-3 pb-3 border-b border-slate-100">By <?=htmlspecialchars($book['author'])?></p>
                                <p class="text-sm text-slate-600 flex-grow mb-4 line-clamp-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?=htmlspecialchars($book['description'])?></p>
                                
                                <div class="flex gap-2 mt-auto">
                                <button onclick="event.stopPropagation(); toggleBookmark(<?=$book['id']?>, this)" class="p-2.5 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors bg-white">
                                    <i class="bi bi-bookmark"></i>
                                </button>
                                <a href="download.php?id=<?=$book['id']?>" target="_blank" onclick="event.stopPropagation();" class="flex-grow flex items-center justify-center bg-brand-900 hover:bg-brand-800 text-white rounded-xl font-semibold text-sm transition-colors text-decoration-none">
                                    <i class="bi bi-cloud-arrow-down mr-2"></i> Access
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- NEW LOAD MORE BUTTON -->
        <div id="load-more-container" class="text-center mt-12 <?= $hasMore ? '' : 'hidden' ?>">
            <button id="load-more-btn" class="px-8 py-3 bg-white border border-slate-200 text-brand-900 font-bold rounded-full hover:bg-slate-50 transition-colors shadow-sm">
                Load More Books <i class="bi bi-arrow-down-short ml-1"></i>
            </button>
        </div>

    </div>
</main>

<?php include __DIR__.'/partials/footer.php'; ?>

<script> const MUTCU = { user: <?=json_encode($currentUser)?>, books: [] };</script>
<script src="assets/js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>