<?php
require_once __DIR__ . '/functions.php';
$currentUser = currentUser();

// OPTIMIZED: We only fetch the first 4 books and 3 articles using our new Limit parameter
$books = getBooks(4);
$articles = getArticles(3);
$stats = getStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MUTCU E-Library | Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { heading: ['Montserrat', 'sans-serif'], body: ['Lato', 'sans-serif'], }, colors: { brand: { 900: '#0f172a', 800: '#1e293b', 50: '#f8fafc', }, accent: { 500: '#f97316', 600: '#ea580c', } } } } }
    </script>
    <style> h1, h2, h3, h4, h5, h6 { font-family: 'Montserrat', sans-serif; } </style>
</head>
<body class="font-body bg-brand-50 flex flex-col min-h-screen text-slate-800">
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <main class="flex-grow">
        <section class="relative bg-brand-900 text-white overflow-hidden py-24 sm:py-32 w-full">
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&q=80" alt="Library" class="w-full h-full object-cover opacity-20 mix-blend-overlay">
                <div class="absolute inset-0 bg-gradient-to-t from-brand-900 via-brand-900/80 to-transparent"></div>
            </div>

            <div class="relative z-10 max-w-4xl mx-auto px-4 text-center">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold bg-accent-500/10 text-accent-500 border border-accent-500/20 mb-6 backdrop-blur-sm">
                    <span class="relative flex h-2.5 w-2.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent-500 opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-accent-500"></span></span>
                    Official Digital Platform
                </span>
                
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold font-heading tracking-tight mb-6">Equipping Leaders,<br/><span class="text-transparent bg-clip-text bg-gradient-to-r from-accent-500 to-yellow-400">Deepening Faith</span></h1>
                
                <p class="text-lg sm:text-xl text-slate-300 max-w-2xl mx-auto mb-10 leading-relaxed">Access curated, Christ-centered educational resources, books, and insights from leading authors to foster your spiritual, academic, and leadership growth.</p>
                
                <form action="library.php" method="GET" class="max-w-2xl mx-auto relative group">
                    <div class="relative flex items-center shadow-2xl">
                        <input type="text" name="q" class="w-full py-4 pl-6 pr-16 rounded-full bg-white/10 border border-white/20 text-white placeholder-slate-400 backdrop-blur-md focus:outline-none focus:ring-2 focus:ring-accent-500 focus:bg-white/20 transition-all" placeholder="Search by title, author, or keyword..." />
                        <button type="submit" class="absolute right-2 p-3 rounded-full bg-accent-500 hover:bg-accent-600 text-white transition-colors duration-300 border-0">
                            <i class="bi bi-search text-lg"></i>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="py-20 bg-brand-50 w-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold font-heading text-brand-900 mb-2">Explore by Category</h2>
                <div class="w-16 h-1 bg-accent-500 mx-auto rounded-full mb-12"></div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <?php foreach (['Faith' => ['icon' => 'bi-heart-fill', 'text' => 'text-rose-500', 'bg' => 'bg-rose-100'], 'Leadership' => ['icon' => 'bi-graph-up-arrow', 'text' => 'text-emerald-500', 'bg' => 'bg-emerald-100'], 'Purpose' => ['icon' => 'bi-compass-fill', 'text' => 'text-blue-500', 'bg' => 'bg-blue-100'], 'Relationships' => ['icon' => 'bi-people-fill', 'text' => 'text-purple-500', 'bg' => 'bg-purple-100']] as $category => $data): ?>
                        <div onclick="location.href='library.php?category=<?=urlencode($category)?>'" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 hover:border-slate-300 transition-all duration-300 cursor-pointer group">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full <?= $data['bg'] ?> <?= $data['text'] ?> mb-4 group-hover:scale-110 transition-transform duration-300"><i class="bi <?= $data['icon'] ?> text-2xl"></i></div>
                            <h3 class="font-bold font-heading text-slate-800"><?= $category ?></h3>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="py-20 bg-white border-t border-slate-200 w-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10">
                    <div>
                        <h2 class="text-3xl font-bold font-heading text-brand-900 mb-2">Featured Books</h2>
                        <div class="w-16 h-1 bg-accent-500 rounded-full"></div>
                    </div>
                    <a href="library.php" class="hidden sm:inline-flex items-center text-sm font-bold text-accent-500 hover:text-accent-600 transition-colors text-decoration-none">View All Catalog <i class="bi bi-arrow-right ml-1"></i></a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <?php foreach (array_slice($books, 0, 4) as $book): ?>
                        <div class="flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer group" onclick="openQuickView(this)" data-book-id="<?=$book['id']?>" data-title="<?=htmlspecialchars($book['title'])?>" data-author="<?=htmlspecialchars($book['author'])?>" data-description="<?=htmlspecialchars($book['description'])?>" data-cover="<?=htmlspecialchars($book['cover'])?>" data-category="<?=htmlspecialchars($book['category'])?>" data-drive-link="<?=htmlspecialchars($book['drive_link'])?>">
                            <div class="relative pt-[130%] bg-slate-100 overflow-hidden">
                                <span class="absolute top-3 right-3 z-10 bg-brand-900/80 backdrop-blur-sm text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider"><?=htmlspecialchars($book['category'])?></span>
                                <img src="<?=htmlspecialchars($book['cover'])?>" alt="Cover" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>
                            <div class="p-5 flex flex-col flex-grow">
                                <h4 class="font-bold font-heading text-lg text-brand-900 mb-1 truncate"><?=htmlspecialchars($book['title'])?></h4>
                                <p class="text-sm text-slate-500 mb-3 pb-3 border-b border-slate-100">By <?=htmlspecialchars($book['author'])?></p>
                                <div class="flex gap-2 mt-auto">
                                    <button onclick="event.stopPropagation(); toggleBookmark(<?=$book['id']?>, this)" class="p-2.5 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors bg-white"><i class="bi bi-bookmark"></i></button>
                                    <a href="download.php?id=<?=$book['id']?>" target="_blank" onclick="event.stopPropagation();" class="flex-grow flex items-center justify-center bg-brand-900 hover:bg-brand-800 text-white rounded-xl font-semibold text-sm transition-colors text-decoration-none"><i class="bi bi-cloud-arrow-down mr-2"></i> Access</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="library.php" class="sm:hidden mt-8 w-full inline-flex justify-center items-center py-3 px-4 rounded-xl bg-accent-500/10 text-accent-600 font-bold text-sm text-decoration-none">View All Catalog <i class="bi bi-arrow-right ml-2"></i></a>
            </div>
        </section>
    </main>
    
    <?php include __DIR__ . '/partials/footer.php'; ?>
    <script> const MUTCU = { user: <?=json_encode($currentUser)?> };</script>
    <script src="assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>