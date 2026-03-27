<?php
require_once __DIR__ . '/functions.php';
$currentUser = currentUser();
if (!$currentUser) {
    header('Location: login.php');
    exit;
}
$bookmarks = getUserBookmarks($currentUser['id']);
$history = getUserReadingHistory($currentUser['id']);
$stats = getStats();
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Calculate reading progress safely
$downloadedBooks = count(array_unique(array_column($history, 'id'))); 
$goal = $currentUser['reading_goal'] ?? 0;
$progress = $goal > 0 ? min(100, ($downloadedBooks / $goal) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MUTCU E-Library | My Library</title>
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
    
    <main class="flex-grow py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <?php if ($flash_success): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative mb-6 flex items-center shadow-sm">
                <i class="bi bi-check-circle-fill mr-3 text-lg"></i>
                <span class="block sm:inline font-medium"><?php echo htmlspecialchars($flash_success); ?></span>
            </div>
            <?php endif; ?>

            <div class="flex flex-col lg:flex-row gap-8 mb-16">
                <!-- Header Info -->
                <div class="w-full lg:w-1/2 flex flex-col justify-center">
                    <h2 class="text-3xl font-extrabold font-heading text-brand-900 mb-2">My Library Dashboard</h2>
                    <div class="w-16 h-1 bg-accent-500 rounded-full mb-4"></div>
                    <p class="text-slate-500 text-lg leading-relaxed">Welcome back, <strong class="text-brand-900"><?=htmlspecialchars($currentUser['name'])?></strong>. Track your reading progress and manage your bookmarks.</p>
                </div>
                
                <!-- Goals & Settings -->
                <div class="w-full lg:w-1/2 flex flex-col sm:flex-row gap-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex-1">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 mr-3">
                                <i class="bi bi-trophy-fill text-lg"></i>
                            </div>
                            <h5 class="font-bold font-heading text-brand-900 m-0">Reading Goal</h5>
                        </div>
                        <div class="mb-5">
                            <div class="flex justify-between text-sm font-semibold text-slate-600 mb-2">
                                <span>Read: <?=$downloadedBooks?></span>
                                <span>Goal: <?=$goal ?: 'Not Set'?></span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden border border-slate-200">
                                <div class="bg-emerald-500 h-full rounded-full transition-all duration-1000 ease-out" style="width: <?=$progress?>%;"></div>
                            </div>
                        </div>
                        <form method="post" action="actions.php" class="flex gap-2">
                            <input type="hidden" name="action" value="update_goal">
                            <input type="number" name="goal" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none text-sm transition-all" placeholder="Annual goal" value="<?=$goal?>" min="1" required>
                            <button type="submit" class="px-4 py-2 bg-accent-500 hover:bg-accent-600 text-white font-bold rounded-xl text-sm transition-colors shadow-md border-0">Update</button>
                        </form>
                    </div>

                    <div class="bg-brand-900 p-6 rounded-2xl shadow-sm border border-brand-800 flex-1 flex flex-col justify-center text-white">
                        <h5 class="font-bold font-heading mb-2">Account Settings</h5>
                        <p class="text-slate-400 text-sm mb-4">Update your name, email, or password credentials.</p>
                        <button type="button" class="w-full py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl font-bold text-sm transition-colors" data-bs-toggle="modal" data-bs-target="#profileSettingsModal">
                            <i class="bi bi-gear-fill mr-2"></i> Edit Profile
                        </button>
                    </div>
                </div>
            </div>

            <!-- Saved Books Section -->
            <div class="mb-16">
                <div class="flex items-center mb-8">
                    <i class="bi bi-bookmark-star-fill text-2xl text-accent-500 mr-3"></i>
                    <h3 class="text-2xl font-extrabold font-heading text-brand-900 m-0">Saved Books</h3>
                </div>
                
                <?php if ($bookmarks): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                        <?php foreach ($bookmarks as $book): ?>
                            <div class="flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                                <div class="relative pt-[130%] bg-slate-100 overflow-hidden cursor-pointer" onclick="openQuickView(this)" data-book-id="<?=$book['id']?>" data-title="<?=htmlspecialchars($book['title'])?>" data-author="<?=htmlspecialchars($book['author'])?>" data-description="<?=htmlspecialchars($book['description'])?>" data-cover="<?=htmlspecialchars($book['cover'])?>" data-category="<?=htmlspecialchars($book['category'])?>" data-drive-link="<?=htmlspecialchars($book['drive_link'])?>">
                                    <span class="absolute top-3 right-3 z-10 bg-brand-900/80 backdrop-blur-sm text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                                        <?=htmlspecialchars($book['category'])?>
                                    </span>
                                    <img src="<?=htmlspecialchars($book['cover'])?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                </div>
                                <div class="p-5 flex flex-col flex-grow">
                                    <h4 class="font-bold font-heading text-lg text-brand-900 mb-1 truncate"><?=htmlspecialchars($book['title'])?></h4>
                                    <p class="text-sm text-slate-500 mb-3 pb-3 border-b border-slate-100">By <?=htmlspecialchars($book['author'])?></p>
                                    
                                    <!-- NEW: Read Status Selector -->
                                    <div class="mb-4" onclick="event.stopPropagation();">
                                        <select onchange="updateReadStatus(<?=$book['id']?>, this.value)" class="w-full text-xs font-bold text-slate-600 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 outline-none focus:border-accent-500 transition-colors cursor-pointer">
                                            <option value="to_read" <?=($book['status']??'to_read')==='to_read'?'selected':''?>>📚 Want to Read</option>
                                            <option value="reading" <?=($book['status']??'')==='reading'?'selected':''?>>📖 Currently Reading</option>
                                            <option value="finished" <?=($book['status']??'')==='finished'?'selected':''?>>✅ Finished</option>
                                        </select>
                                    </div>

                                    <div class="flex gap-2 mt-auto">
                                        <button onclick="event.stopPropagation(); toggleBookmark(<?=$book['id']?>, this); setTimeout(()=>window.location.reload(), 500);" class="w-full py-2.5 rounded-xl border border-slate-200 text-rose-500 hover:bg-rose-50 hover:border-rose-200 font-bold text-sm transition-colors flex items-center justify-center">
                                            <i class="bi bi-bookmark-x mr-2"></i> Remove
                                        </button>
                                        <a href="download.php?id=<?=$book['id']?>" target="_blank" onclick="event.stopPropagation();" class="w-full py-2.5 rounded-xl bg-brand-900 hover:bg-brand-800 text-white font-bold text-sm transition-colors flex items-center justify-center text-decoration-none">
                                            <i class="bi bi-cloud-arrow-down mr-2"></i> Access
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-16 bg-white rounded-2xl border border-slate-200 shadow-sm">
                        <i class="bi bi-bookmark-x text-slate-300 text-6xl mb-4 block"></i>
                        <h3 class="font-extrabold font-heading text-2xl text-brand-900 mb-2">No Saved Books</h3>
                        <p class="text-slate-500 mb-6">Start bookmarking books to build your personal library collection.</p>
                        <a href="library.php" class="inline-flex px-8 py-3 bg-accent-500 hover:bg-accent-600 text-white rounded-full font-bold transition-colors text-decoration-none shadow-lg shadow-accent-500/30">Browse Catalog</a>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    </main>

    <!-- Settings Modal -->
    <div class="modal fade" id="profileSettingsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-2xl border-0 shadow-2xl">
                <div class="modal-header bg-brand-50 border-b border-slate-100 p-4">
                    <h5 class="modal-title font-heading font-bold text-brand-900"><i class="bi bi-person-gear mr-2 text-accent-500"></i> Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="actions.php">
                    <div class="modal-body p-6">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
                            <input type="text" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none" name="name" value="<?=htmlspecialchars($currentUser['name'])?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                            <input type="email" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none" name="email" value="<?=htmlspecialchars($currentUser['email'])?>" required>
                        </div>
                        <div class="mb-2">
                            <label class="block text-sm font-bold text-slate-700 mb-2">New Password <span class="text-slate-400 font-normal">(Optional)</span></label>
                            <input type="password" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none" name="password" placeholder="Leave blank to keep current">
                        </div>
                    </div>
                    <div class="modal-footer bg-brand-50 border-t border-slate-100 p-4">
                        <button type="button" class="px-5 py-2 rounded-xl font-bold text-slate-600 hover:bg-slate-200 transition-colors" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-accent-500 hover:bg-accent-600 text-white rounded-xl font-bold transition-colors shadow-md border-0">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/partials/footer.php'; ?>
    <script> const MUTCU = { user: <?=json_encode($currentUser)?> };</script>
    <script src="assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>