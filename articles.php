<?php
require_once __DIR__ . '/functions.php';
$currentUser = currentUser();
$articles = getArticles();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUTCU E-Library | Articles</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS for modals/toasts fallback -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { heading: ['Montserrat', 'sans-serif'], body: ['Lato', 'sans-serif'], },
                    colors: {
                        brand: { 900: '#0f172a', 800: '#1e293b', 50: '#f8fafc', },
                        accent: { 500: '#f97316', 600: '#ea580c', }
                    }
                }
            }
        }
    </script>
    <style> h1, h2, h3, h4, h5, h6 { font-family: 'Montserrat', sans-serif; } </style>
</head>
<body class="font-body bg-brand-50 flex flex-col min-h-screen text-slate-800">
    <?php include __DIR__.'/partials/header.php'; ?>
    
    <main class="flex-grow py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Page Header -->
            <div class="mb-12">
                <h2 class="text-3xl font-extrabold font-heading text-brand-900 mb-2">Christian Articles</h2>
                <div class="w-16 h-1 bg-accent-500 rounded-full mb-4"></div>
                <p class="text-slate-500 text-lg">Read inspiring thoughts and external articles from leading Christian authors.</p>
            </div>

            <!-- Articles Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="articles-grid">
                <?php foreach ($articles as $art): ?>
                    <div class="flex flex-col bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        <div class="flex justify-between items-center mb-5">
                            <span class="bg-rose-100 text-rose-600 text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider flex items-center">
                                <i class="bi bi-pen mr-1.5 text-sm"></i> Article
                            </span>
                            <span class="text-xs font-semibold text-slate-500 flex items-center bg-slate-100 px-3 py-1.5 rounded-full">
                                <i class="bi bi-clock mr-1.5"></i> <?=htmlspecialchars($art['read_time'] ?? '5 min read')?>
                            </span>
                        </div>
                        
                        <h4 class="text-xl font-bold font-heading text-brand-900 mb-3 leading-snug"><?=htmlspecialchars($art['title'])?></h4>
                        <p class="text-sm text-slate-500 mb-4 border-b border-slate-100 pb-4">
                            By <strong class="text-slate-700"><?=htmlspecialchars($art['author'])?></strong> &bull; <?=htmlspecialchars($art['date'])?>
                        </p>
                        <p class="text-sm text-slate-600 flex-grow mb-6 leading-relaxed"><?=htmlspecialchars($art['abstract'])?></p>
                        
                        <a href="article.php?id=<?=$art['id']?>" target="_blank" class="mt-auto inline-flex items-center justify-center w-full py-3 px-4 bg-brand-50 hover:bg-brand-900 text-brand-900 hover:text-white rounded-xl text-sm font-bold transition-colors group text-decoration-none border border-slate-200 hover:border-brand-900">
                            Read Full Article <i class="bi bi-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($articles)): ?>
                <div class="text-center py-16 bg-white rounded-2xl border border-slate-200">
                    <i class="bi bi-file-earmark-x text-slate-300 text-6xl mb-4 block"></i>
                    <h3 class="font-extrabold font-heading text-2xl text-brand-900 mb-2">No articles found</h3>
                    <p class="text-slate-500">Check back later for new insights and publications.</p>
                </div>
            <?php endif; ?>

        </div>
    </main>
    
    <?php include __DIR__.'/partials/footer.php'; ?>
    
    <script> const MUTCU = { user: <?=json_encode($currentUser)?>, articles: <?=json_encode($articles)?> };</script>
    <script src="assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>