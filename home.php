<?php
require_once __DIR__ . '/functions.php';
$currentUser = currentUser();

// OPTIMIZED: Fetch explicitly featured items for a curated homepage experience
$books = getFeaturedBooks(4);
$articles = getFeaturedArticles(3);
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
        tailwind.config = { theme: { extend: { fontFamily: { heading: ['Montserrat', 'sans-serif'], body: ['Lato', 'sans-serif'], }, colors: { brand: { 900: '#060B26', 800: '#0B133A', 50: '#F4F6FB', }, accent: { 500: '#FF9800', 600: '#E68A00', }, mutcu: { teal: '#2DD4BF', red: '#FF1A35' } } } } }
    </script>
    <style> 
        h1, h2, h3, h4, h5, h6 { font-family: 'Montserrat', sans-serif; } 
        .hero-glow { animation: pulse-glow 4s infinite alternate; }
        @keyframes pulse-glow { 0% { opacity: 0.4; transform: scale(1); } 100% { opacity: 0.7; transform: scale(1.05); } }
    </style>
</head>
<body class="font-body bg-brand-50 flex flex-col min-h-screen text-slate-800">
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <main class="flex-grow">
        <!-- Modernized Hero Section -->
        <section class="relative bg-brand-900 text-white overflow-hidden py-24 sm:py-36 w-full">
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&q=80" alt="Library background" class="w-full h-full object-cover opacity-10 mix-blend-luminosity">
                <div class="absolute inset-0 bg-gradient-to-b from-brand-900/80 via-brand-900 to-brand-900/95"></div>
                <!-- Animated Glowing Orbs -->
                <div class="absolute top-[10%] left-[20%] w-[500px] h-[500px] bg-accent-500/20 rounded-full blur-[100px] hero-glow pointer-events-none"></div>
                <div class="absolute bottom-[-10%] right-[10%] w-[400px] h-[400px] bg-mutcu-teal/10 rounded-full blur-[100px] hero-glow pointer-events-none" style="animation-delay: 2s;"></div>
            </div>

            <div class="relative z-10 max-w-4xl mx-auto px-4 text-center">
                <?php if ($currentUser): ?>
                    <span class="inline-block mb-4 px-4 py-1.5 rounded-full bg-white/10 border border-white/20 text-white font-semibold text-sm backdrop-blur-sm animate-fade-in-down">
                        Welcome back, <span class="text-accent-500"><?=htmlspecialchars($currentUser['name'])?></span>! 👋
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold bg-accent-500/10 text-accent-500 border border-accent-500/20 mb-6 backdrop-blur-sm">
                        <span class="relative flex h-2.5 w-2.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent-500 opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-accent-500"></span></span>
                        Official Digital Platform
                    </span>
                <?php endif; ?>
                
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold font-heading tracking-tight mb-6 leading-tight">
                    Equipping Leaders,<br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-accent-500 to-yellow-400">Deepening Faith</span>
                </h1>
                
                <p class="text-lg sm:text-xl text-slate-300 max-w-2xl mx-auto mb-10 leading-relaxed font-medium">
                    Access curated, Christ-centered educational resources, books, and insights from leading authors to foster your spiritual, academic, and leadership growth.
                </p>
                
                <form action="library.php" method="GET" class="max-w-2xl mx-auto relative group">
                    <div class="relative flex items-center shadow-2xl hover:shadow-accent-500/10 transition-shadow duration-500 rounded-full">
                        <input type="text" name="q" class="w-full py-4.5 pl-6 pr-16 rounded-full bg-white/10 border border-white/20 text-white placeholder-slate-400 backdrop-blur-md focus:outline-none focus:ring-2 focus:ring-accent-500 focus:bg-white/20 transition-all font-medium" placeholder="Search by title, author, or keyword..." />
                        <button type="submit" class="absolute right-2 p-3 rounded-full bg-accent-500 hover:bg-accent-600 text-white transition-colors duration-300 border-0 flex items-center justify-center">
                            <i class="bi bi-search text-lg font-bold"></i>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="py-20 bg-brand-50 w-full relative z-20 -mt-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <?php foreach (['Faith' => ['icon' => 'bi-heart-fill', 'text' => 'text-mutcu-red', 'bg' => 'bg-red-50'], 'Leadership' => ['icon' => 'bi-graph-up-arrow', 'text' => 'text-emerald-500', 'bg' => 'bg-emerald-50'], 'Purpose' => ['icon' => 'bi-compass-fill', 'text' => 'text-mutcu-teal', 'bg' => 'bg-teal-50'], 'Relationships' => ['icon' => 'bi-people-fill', 'text' => 'text-purple-500', 'bg' => 'bg-purple-50']] as $category => $data): ?>
                        <div onclick="location.href='library.php?category=<?=urlencode($category)?>'" class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-2 hover:border-slate-200 transition-all duration-300 cursor-pointer group">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl <?= $data['bg'] ?> <?= $data['text'] ?> mb-4 group-hover:scale-110 transition-transform duration-300 shadow-inner"><i class="bi <?= $data['icon'] ?> text-2xl"></i></div>
                            <h3 class="font-bold font-heading text-brand-900"><?= $category ?></h3>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Spotlight / Featured Books Section -->
        <section class="py-20 bg-white border-t border-slate-100 w-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10">
                    <div>
                        <span class="text-accent-500 font-bold uppercase tracking-wider text-xs mb-1 block"><i class="bi bi-star-fill mr-1"></i> Curated Collection</span>
                        <h2 class="text-3xl font-extrabold font-heading text-brand-900 mb-2">Featured Books</h2>
                        <div class="w-16 h-1.5 bg-accent-500 rounded-full"></div>
                    </div>
                    <a href="library.php" class="hidden sm:inline-flex items-center px-5 py-2.5 bg-brand-50 hover:bg-brand-900 text-brand-900 hover:text-white rounded-full text-sm font-bold transition-colors group text-decoration-none border border-slate-200 hover:border-brand-900">
                        Browse Full Catalog <i class="bi bi-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <?php foreach ($books as $book): ?>
                        <div class="flex flex-col bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 cursor-pointer group" onclick="openQuickView(this)" data-book-id="<?=$book['id']?>" data-title="<?=htmlspecialchars($book['title'])?>" data-author="<?=htmlspecialchars($book['author'])?>" data-description="<?=htmlspecialchars($book['description'])?>" data-cover="<?=htmlspecialchars($book['cover'])?>" data-category="<?=htmlspecialchars($book['category'])?>" data-drive-link="<?=htmlspecialchars($book['drive_link'])?>">
                            
                            <div class="relative pt-[140%] bg-slate-100 overflow-hidden">
                                <?php if($book['is_featured']): ?>
                                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-accent-500 to-yellow-400 z-20"></div>
                                <?php endif; ?>
                                <span class="absolute top-4 right-4 z-10 bg-brand-900/90 backdrop-blur-md text-white text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wider shadow-lg">
                                    <?=htmlspecialchars($book['category'])?>
                                </span>
                                <img src="<?=htmlspecialchars($book['cover'])?>" alt="Cover" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-brand-900/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                            
                            <div class="p-6 flex flex-col flex-grow relative bg-white">
                                <h4 class="font-extrabold font-heading text-lg text-brand-900 mb-1 leading-tight line-clamp-1 group-hover:text-accent-500 transition-colors"><?=htmlspecialchars($book['title'])?></h4>
                                <p class="text-sm text-slate-500 mb-4 font-medium">By <?=htmlspecialchars($book['author'])?></p>
                                
                                <div class="flex gap-2 mt-auto pt-4 border-t border-slate-100">
                                    <button onclick="event.stopPropagation(); toggleBookmark(<?=$book['id']?>, this)" class="p-3 rounded-xl border border-slate-200 text-slate-400 hover:text-accent-500 hover:bg-accent-50 hover:border-accent-200 transition-all bg-white" title="Save to Library">
                                        <i class="bi bi-bookmark-plus text-lg"></i>
                                    </button>
                                    <a href="download.php?id=<?=$book['id']?>" target="_blank" onclick="event.stopPropagation();" class="flex-grow flex items-center justify-center bg-brand-900 hover:bg-brand-800 text-white rounded-xl font-bold text-sm transition-colors text-decoration-none shadow-md shadow-brand-900/20">
                                        <i class="bi bi-cloud-arrow-down mr-2"></i> Access Resource
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <a href="library.php" class="sm:hidden mt-8 w-full inline-flex justify-center items-center py-3.5 px-4 rounded-xl bg-accent-500/10 text-accent-600 font-bold text-sm text-decoration-none">
                    Browse Full Catalog <i class="bi bi-arrow-right ml-2"></i>
                </a>
            </div>
        </section>

        <!-- NEW: Featured Articles Section -->
        <?php if (!empty($articles)): ?>
        <section class="py-20 bg-brand-50 w-full border-t border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10">
                    <div>
                        <span class="text-mutcu-red font-bold uppercase tracking-wider text-xs mb-1 block"><i class="bi bi-journal-text mr-1"></i> Latest Insights</span>
                        <h2 class="text-3xl font-extrabold font-heading text-brand-900 mb-2">Featured Articles</h2>
                        <div class="w-16 h-1.5 bg-mutcu-red rounded-full"></div>
                    </div>
                    <a href="articles.php" class="hidden sm:inline-flex items-center text-sm font-bold text-mutcu-red hover:text-red-700 transition-colors text-decoration-none">
                        Read All Articles <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <?php foreach ($articles as $article): ?>
                        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col group relative overflow-hidden">
                            <?php if($article['is_featured']): ?>
                                <div class="absolute top-0 right-0 w-16 h-16 overflow-hidden">
                                    <div class="absolute top-0 right-0 bg-mutcu-red text-white text-[10px] font-bold py-1 w-24 text-center transform translate-x-6 translate-y-3 rotate-45 shadow-sm uppercase tracking-wider">Top</div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex justify-between items-center mb-5">
                                <span class="bg-rose-50 text-mutcu-red text-xs font-bold px-3 py-1 rounded-full flex items-center">
                                    <i class="bi bi-pen mr-1"></i> Article
                                </span>
                                <span class="flex items-center text-xs text-slate-400 font-semibold bg-slate-50 px-2 py-1 rounded-md">
                                    <i class="bi bi-clock mr-1"></i> <?=htmlspecialchars($article['read_time'] ?? '5 min')?>
                                </span>
                            </div>
                            
                            <h3 class="text-xl font-extrabold font-heading text-brand-900 mb-3 leading-snug group-hover:text-accent-500 transition-colors line-clamp-2"><?=htmlspecialchars($article['title'])?></h3>
                            <p class="text-sm text-slate-500 mb-4 pb-4 border-b border-slate-100 font-medium">By <span class="text-slate-800"><?=htmlspecialchars($article['author'])?></span> &bull; <?=htmlspecialchars($article['date'])?></p>
                            <p class="text-slate-600 text-sm flex-grow mb-6 leading-relaxed line-clamp-3"><?=htmlspecialchars($article['abstract'])?></p>
                            
                            <a href="article.php?id=<?=$article['id']?>" target="_blank" class="inline-flex items-center text-accent-500 font-bold text-sm hover:text-brand-900 transition-colors mt-auto w-fit group-hover:underline text-decoration-none">
                                Read Full Article <i class="bi bi-arrow-right ml-1 transform group-hover:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

    </main>
    
    <?php include __DIR__ . '/partials/footer.php'; ?>
    <script> const MUTCU = { user: <?=json_encode($currentUser)?> };</script>
    <script src="assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>