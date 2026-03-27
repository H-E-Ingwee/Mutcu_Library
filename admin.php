<?php
require_once __DIR__ . '/functions.php';
$currentUser = currentUser();
if (!$currentUser || !isAdmin()) {
    header('Location: login.php');
    exit;
}
$books = getBooks();
$articles = getArticles();
$stats = getStats();
$categoryData = getCategoryDistribution();
$weeklyData = getWeeklyInteractions();
$eventsStmt = $pdo->query('SELECT event_type, target_type, COUNT(*) as count FROM events GROUP BY event_type, target_type');
$events = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);
$recentEventsStmt = $pdo->query('SELECT e.event_type, e.target_type, e.created_at, u.name as user_name FROM events e LEFT JOIN users u ON e.user_id = u.id ORDER BY e.created_at DESC LIMIT 10');
$recentEvents = $recentEventsStmt->fetchAll(PDO::FETCH_ASSOC);
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUTCU E-Library | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { heading: ['Montserrat', 'sans-serif'], body: ['Lato', 'sans-serif'], },
                    colors: { brand: { 900: '#0f172a', 800: '#1e293b', 50: '#f8fafc', }, accent: { 500: '#f97316', 600: '#ea580c', } }
                }
            }
        }
    </script>
    <style> h1, h2, h3, h4, h5, h6 { font-family: 'Montserrat', sans-serif; } </style>
</head>
<body class="font-body bg-brand-50 flex flex-col min-h-screen text-slate-800">
    <?php include __DIR__.'/partials/header.php'; ?>
    
    <main class="flex-grow py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Dashboard Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 pb-6 border-b border-slate-200">
                <div>
                    <h2 class="text-3xl font-extrabold font-heading text-brand-900 flex items-center mb-2">
                        <i class="bi bi-speedometer2 text-accent-500 mr-3"></i> Admin Dashboard
                    </h2>
                    <p class="text-slate-500">Manage library inventory, articles, and analyze system metrics.</p>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-brand-900 text-white rounded-2xl p-6 shadow-sm border border-brand-800 hover:-translate-y-1 transition-transform">
                    <div class="flex justify-between items-center mb-4">
                        <h6 class="text-slate-400 font-bold uppercase text-xs tracking-wider m-0">Total Books</h6>
                        <i class="bi bi-journals text-2xl text-accent-500"></i>
                    </div>
                    <h2 class="text-4xl font-extrabold font-heading m-0"><?= $stats['total_books'] ?></h2>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:-translate-y-1 transition-transform">
                    <div class="flex justify-between items-center mb-4">
                        <h6 class="text-slate-400 font-bold uppercase text-xs tracking-wider m-0">Total Articles</h6>
                        <i class="bi bi-file-text text-2xl text-rose-500"></i>
                    </div>
                    <h2 class="text-4xl font-extrabold font-heading text-brand-900 m-0"><?= $stats['total_articles'] ?></h2>
                </div>
                <div class="bg-emerald-500 text-white rounded-2xl p-6 shadow-sm hover:-translate-y-1 transition-transform">
                    <div class="flex justify-between items-center mb-4">
                        <h6 class="text-emerald-100 font-bold uppercase text-xs tracking-wider m-0">Total Users</h6>
                        <i class="bi bi-people text-2xl text-white"></i>
                    </div>
                    <h2 class="text-4xl font-extrabold font-heading m-0"><?= $stats['total_users'] ?></h2>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:-translate-y-1 transition-transform">
                    <div class="flex justify-between items-center mb-4">
                        <h6 class="text-slate-400 font-bold uppercase text-xs tracking-wider m-0">Downloads</h6>
                        <i class="bi bi-cloud-arrow-down text-2xl text-blue-500"></i>
                    </div>
                    <h2 class="text-4xl font-extrabold font-heading text-brand-900 m-0"><?= $stats['total_downloads'] ?: 0 ?></h2>
                </div>
            </div>

            <!-- Custom Styled Tabs -->
            <ul class="nav nav-pills flex flex-wrap gap-2 mb-8 border-b border-slate-200 pb-4" id="adminTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-xl px-5 py-2.5 font-bold text-sm bg-slate-100 text-slate-600 focus:bg-brand-900 focus:text-white aria-selected:bg-brand-900 aria-selected:text-white transition-colors border-0" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics" type="button" role="tab">Analytics</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-xl px-5 py-2.5 font-bold text-sm bg-slate-100 text-slate-600 focus:bg-brand-900 focus:text-white aria-selected:bg-brand-900 aria-selected:text-white transition-colors border-0" id="books-tab" data-bs-toggle="tab" data-bs-target="#manage-books" type="button" role="tab">Manage Books</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-xl px-5 py-2.5 font-bold text-sm bg-slate-100 text-slate-600 focus:bg-brand-900 focus:text-white aria-selected:bg-brand-900 aria-selected:text-white transition-colors border-0" id="articles-tab" data-bs-toggle="tab" data-bs-target="#manage-articles" type="button" role="tab">Manage Articles</button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="adminTabsContent">
                
                <!-- Analytics Tab -->
                <div class="tab-pane fade show active" id="analytics" role="tabpanel">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                            <h5 class="font-bold font-heading text-brand-900 mb-6 border-b border-slate-100 pb-4">User Interactions Over Time</h5>
                            <div class="relative h-72 w-full"><canvas id="interactionsChart"></canvas></div>
                        </div>
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col">
                            <h5 class="font-bold font-heading text-brand-900 mb-6 border-b border-slate-100 pb-4">Activity Totals</h5>
                            <div class="flex-grow space-y-3">
                                <?php foreach ($events as $event): ?>
                                    <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                                        <span class="font-semibold text-slate-700 text-sm"><?=htmlspecialchars(ucfirst($event['event_type']) . ' '. ucfirst($event['target_type']))?></span>
                                        <span class="bg-brand-900 text-white text-xs font-bold px-3 py-1 rounded-full"><?= $event['count'] ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                            <h5 class="font-bold font-heading text-brand-900 mb-6 border-b border-slate-100 pb-4">Categories Distribution</h5>
                            <div class="relative h-64 w-full"><canvas id="categoryChart"></canvas></div>
                        </div>
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                            <h5 class="font-bold font-heading text-brand-900 mb-6 border-b border-slate-100 pb-4">Recent User Activities</h5>
                            <div class="space-y-4">
                                <?php if ($recentEvents): ?>
                                    <?php foreach (array_slice($recentEvents, 0, 5) as $event): ?>
                                        <div class="flex flex-col pb-4 border-b border-slate-100 last:border-0 last:pb-0">
                                            <div class="flex justify-between items-start">
                                                <div class="text-sm text-slate-700">
                                                    <strong class="text-brand-900"><?= htmlspecialchars(ucfirst($event['event_type'])) ?></strong> on <em class="text-accent-600 font-semibold not-italic"><?= htmlspecialchars(ucfirst($event['target_type'])) ?></em>
                                                    <?php if ($event['user_name']): ?> by <?= htmlspecialchars($event['user_name']) ?><?php endif; ?>
                                                </div>
                                                <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded-md ml-2 whitespace-nowrap"><?= htmlspecialchars(date('M d, H:i', strtotime($event['created_at']))) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center text-slate-500 text-sm py-4">No recent activities.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manage Books Tab -->
                <div class="tab-pane fade" id="manage-books" role="tabpanel">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <h5 class="font-bold font-heading text-brand-900 m-0">Book Catalog</h5>
                            <button class="px-5 py-2.5 bg-accent-500 hover:bg-accent-600 text-white font-bold rounded-xl text-sm transition-colors shadow-sm flex items-center" data-bs-toggle="modal" data-bs-target="#addBookModal">
                                <i class="bi bi-plus-circle-fill mr-2 text-lg"></i> Add Book
                            </button>
                        </div>
                        <div class="p-4 bg-slate-50 border-b border-slate-200">
                            <div class="relative max-w-md">
                                <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                                <input type="text" id="searchBooksAdmin" class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-accent-500 outline-none" placeholder="Search catalog...">
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse" id="booksTable">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                                        <th class="p-4 font-bold">ID</th>
                                        <th class="p-4 font-bold">Cover</th>
                                        <th class="p-4 font-bold">Title & Author</th>
                                        <th class="p-4 font-bold">Category</th>
                                        <th class="p-4 font-bold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    <?php foreach ($books as $book): ?>
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                        <td class="p-4 text-slate-500 font-semibold">#<?=$book['id']?></td>
                                        <td class="p-4"><img src="<?=htmlspecialchars($book['cover'])?>" class="w-12 h-16 object-cover rounded shadow-sm border border-slate-200"></td>
                                        <td class="p-4">
                                            <div class="font-bold text-brand-900 mb-1"><?=htmlspecialchars($book['title'])?></div>
                                            <div class="text-slate-500 text-xs"><?=htmlspecialchars($book['author'])?></div>
                                        </td>
                                        <td class="p-4"><span class="bg-slate-100 text-slate-700 text-xs font-bold px-2 py-1 rounded-md border border-slate-200"><?=htmlspecialchars($book['category'])?></span></td>
                                        <td class="p-4 text-right space-x-2">
                                            <a href="download.php?id=<?=$book['id']?>" target="_blank" class="inline-flex p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors text-decoration-none" title="View Link"><i class="bi bi-link-45deg"></i></a>
                                            <button class="p-2 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition-colors border-0" title="Edit" data-bs-toggle="modal" data-bs-target="#editBookModal" onclick="populateEditBookModal(<?=$book['id']?>, '<?=htmlspecialchars(addslashes($book['title']))?>', '<?=htmlspecialchars(addslashes($book['author']))?>', '<?=htmlspecialchars($book['category'])?>', '<?=htmlspecialchars(addslashes($book['drive_link']))?>', '<?=htmlspecialchars(addslashes($book['description']))?>', '<?=htmlspecialchars(addslashes($book['cover']))?>')"><i class="bi bi-pencil"></i></button>
                                            <form method="post" action="actions.php" class="inline-block m-0">
                                                <input type="hidden" name="action" value="delete_book">
                                                <input type="hidden" name="book_id" value="<?=$book['id']?>">
                                                <input type="hidden" name="return_url" value="admin.php">
                                                <button type="submit" class="p-2 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100 transition-colors border-0" title="Delete" onclick="return confirm('Delete this book?');"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Manage Articles Tab -->
                <div class="tab-pane fade" id="manage-articles" role="tabpanel">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <h5 class="font-bold font-heading text-brand-900 m-0">Articles Catalog</h5>
                            <button class="px-5 py-2.5 bg-accent-500 hover:bg-accent-600 text-white font-bold rounded-xl text-sm transition-colors shadow-sm flex items-center" data-bs-toggle="modal" data-bs-target="#addArticleModal">
                                <i class="bi bi-plus-circle-fill mr-2 text-lg"></i> Add Article
                            </button>
                        </div>
                        <div class="p-4 bg-slate-50 border-b border-slate-200">
                            <div class="relative max-w-md">
                                <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                                <input type="text" id="searchArticlesAdmin" class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-accent-500 outline-none" placeholder="Search articles...">
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse" id="articlesTable">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                                        <th class="p-4 font-bold">ID</th>
                                        <th class="p-4 font-bold">Title & Author</th>
                                        <th class="p-4 font-bold">Date & Time</th>
                                        <th class="p-4 font-bold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    <?php foreach ($articles as $art): ?>
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                        <td class="p-4 text-slate-500 font-semibold">#<?=$art['id']?></td>
                                        <td class="p-4">
                                            <div class="font-bold text-brand-900 mb-1"><?=htmlspecialchars($art['title'])?></div>
                                            <div class="text-slate-500 text-xs">By <?=htmlspecialchars($art['author'])?></div>
                                        </td>
                                        <td class="p-4 text-slate-600">
                                            <div><?=htmlspecialchars($art['date'])?></div>
                                            <div class="text-xs text-slate-400 mt-1"><i class="bi bi-clock"></i> <?=htmlspecialchars($art['read_time'])?></div>
                                        </td>
                                        <td class="p-4 text-right space-x-2">
                                            <a href="article.php?id=<?=$art['id']?>" target="_blank" class="inline-flex p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors text-decoration-none" title="View Link"><i class="bi bi-box-arrow-up-right"></i></a>
                                            <button class="p-2 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition-colors border-0" title="Edit" data-bs-toggle="modal" data-bs-target="#editArticleModal" onclick="populateEditArticleModal(<?=$art['id']?>, '<?=htmlspecialchars(addslashes($art['title']))?>', '<?=htmlspecialchars(addslashes($art['author']))?>', '<?=htmlspecialchars(addslashes($art['abstract']))?>', '<?=htmlspecialchars(addslashes($art['link']))?>', '<?=htmlspecialchars(addslashes($art['date']))?>', '<?=htmlspecialchars(addslashes($art['read_time']))?>')"><i class="bi bi-pencil"></i></button>
                                            <form method="post" action="actions.php" class="inline-block m-0">
                                                <input type="hidden" name="action" value="delete_article">
                                                <input type="hidden" name="article_id" value="<?=$art['id']?>">
                                                <input type="hidden" name="return_url" value="admin.php">
                                                <button type="submit" class="p-2 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100 transition-colors border-0" title="Delete" onclick="return confirm('Delete this article?');"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Admin Modals (Styled with Tailwind) -->
    <!-- Add Book Modal -->
    <div class="modal fade" id="addBookModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-2xl border-0 shadow-2xl overflow-hidden">
                <div class="modal-header bg-brand-900 border-0 p-5">
                    <h5 class="modal-title font-heading font-bold text-white"><i class="bi bi-journal-plus mr-2 text-accent-500"></i> Add New Book</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="actions.php">
                    <div class="modal-body p-6 bg-brand-50 space-y-4">
                        <input type="hidden" name="action" value="add_book"><input type="hidden" name="return_url" value="admin.php">
                        <div><label class="block text-sm font-bold text-slate-700 mb-1">Book Title</label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="title" required></div>
                        <div><label class="block text-sm font-bold text-slate-700 mb-1">Author</label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="author" required></div>
                        <div><label class="block text-sm font-bold text-slate-700 mb-1">Category</label><select class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none bg-white" name="category" required><option value="" disabled selected>Select...</option><option>Faith</option><option>Leadership</option><option>Purpose</option><option>Relationships</option></select></div>
                        <div><label class="block text-sm font-bold text-slate-700 mb-1">Drive Link</label><input type="url" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="drive_link" placeholder="https://drive.google.com/..." required></div>
                        <div><label class="block text-sm font-bold text-slate-700 mb-1">Cover Image URL</label><input type="url" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="cover" placeholder="https://..." required></div>
                        <div><label class="block text-sm font-bold text-slate-700 mb-1">Description</label><textarea class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none h-24" name="description" required></textarea></div>
                    </div>
                    <div class="modal-footer bg-white border-t border-slate-200 p-4">
                        <button type="button" class="px-5 py-2.5 rounded-xl font-bold text-slate-600 hover:bg-slate-100 transition-colors" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-accent-500 hover:bg-accent-600 text-white rounded-xl font-bold transition-colors shadow-sm">Save Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Book Modal -->
    <div class="modal fade" id="editBookModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-2xl border-0 shadow-2xl overflow-hidden">
                <div class="modal-header bg-brand-900 border-0 p-5">
                    <h5 class="modal-title font-heading font-bold text-white"><i class="bi bi-pencil-square mr-2 text-accent-500"></i> Edit Book</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="actions.php">
                    <div class="modal-body p-6 bg-brand-50 space-y-4">
                        <input type="hidden" name="action" value="edit_book"><input type="hidden" name="book_id" id="editBookId"><input type="hidden" name="return_url" value="admin.php">
                        <div><label class="block text-sm font-bold text-slate-700 mb-1">Book Title</label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="title" id="editBookTitle" required></div>
                        <div><label class="block text-sm font-bold text-slate-700 mb-1">Author</label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="author" id="editBookAuthor" required></div>
                        <div><label class="block text-sm font-bold text-slate-700 mb-1">Category</label><select class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none bg-white" name="category" id="editBookCategory" required><option>Faith</option><option>Leadership</option><option>Purpose</option><option>Relationships</option></select></div>
                        <div><label class="block text-sm font-bold text-slate-700 mb-1">Drive Link</label><input type="url" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="drive_link" id="editBookDriveLink" required></div>
                        <div><label class="block text-sm font-bold text-slate-700 mb-1">Cover Image URL</label><input type="url" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="cover" id="editBookCover" required></div>
                        <div><label class="block text-sm font-bold text-slate-700 mb-1">Description</label><textarea class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none h-24" name="description" id="editBookDescription" required></textarea></div>
                    </div>
                    <div class="modal-footer bg-white border-t border-slate-200 p-4">
                        <button type="button" class="px-5 py-2.5 rounded-xl font-bold text-slate-600 hover:bg-slate-100 transition-colors" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-accent-500 hover:bg-accent-600 text-white rounded-xl font-bold transition-colors shadow-sm">Update Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Article Modals omitted for brevity, logic identical to Book Modals -->
    <div class="modal fade" id="addArticleModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-2xl border-0 shadow-2xl overflow-hidden"><div class="modal-header bg-brand-900 p-5"><h5 class="modal-title font-heading font-bold text-white"><i class="bi bi-file-earmark-plus mr-2 text-accent-500"></i> Add Article</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><form method="post" action="actions.php"><div class="modal-body p-6 bg-brand-50 space-y-4"><input type="hidden" name="action" value="add_article"><input type="hidden" name="return_url" value="admin.php"><div><label class="block text-sm font-bold text-slate-700 mb-1">Title</label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="title" required></div><div><label class="block text-sm font-bold text-slate-700 mb-1">Author</label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="author" required></div><div><label class="block text-sm font-bold text-slate-700 mb-1">Link</label><input type="url" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="link" required></div><div><label class="block text-sm font-bold text-slate-700 mb-1">Abstract</label><textarea class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none h-24" name="abstract" required></textarea></div><div class="flex gap-4"><div class="flex-1"><label class="block text-sm font-bold text-slate-700 mb-1">Date</label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="date" value="<?=date('M d, Y')?>" required></div><div class="flex-1"><label class="block text-sm font-bold text-slate-700 mb-1">Read Time</label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="read_time" value="5 min read" required></div></div></div><div class="modal-footer bg-white p-4"><button type="button" class="px-5 py-2.5 rounded-xl font-bold text-slate-600 hover:bg-slate-100" data-bs-dismiss="modal">Cancel</button><button type="submit" class="px-5 py-2.5 bg-accent-500 text-white rounded-xl font-bold">Save Article</button></div></form></div></div></div>
    <div class="modal fade" id="editArticleModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-2xl border-0 shadow-2xl overflow-hidden"><div class="modal-header bg-brand-900 p-5"><h5 class="modal-title font-heading font-bold text-white"><i class="bi bi-pencil-square mr-2 text-accent-500"></i> Edit Article</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><form method="post" action="actions.php"><div class="modal-body p-6 bg-brand-50 space-y-4"><input type="hidden" name="action" value="edit_article"><input type="hidden" name="article_id" id="editArticleId"><input type="hidden" name="return_url" value="admin.php"><div><label class="block text-sm font-bold text-slate-700 mb-1">Title</label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="title" id="editArticleTitle" required></div><div><label class="block text-sm font-bold text-slate-700 mb-1">Author</label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="author" id="editArticleAuthor" required></div><div><label class="block text-sm font-bold text-slate-700 mb-1">Link</label><input type="url" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="link" id="editArticleLink" required></div><div><label class="block text-sm font-bold text-slate-700 mb-1">Abstract</label><textarea class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none h-24" name="abstract" id="editArticleAbstract" required></textarea></div><div class="flex gap-4"><div class="flex-1"><label class="block text-sm font-bold text-slate-700 mb-1">Date</label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="date" id="editArticleDate" required></div><div class="flex-1"><label class="block text-sm font-bold text-slate-700 mb-1">Read Time</label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-accent-500 outline-none" name="read_time" id="editArticleReadTime" required></div></div></div><div class="modal-footer bg-white p-4"><button type="button" class="px-5 py-2.5 rounded-xl font-bold text-slate-600 hover:bg-slate-100" data-bs-dismiss="modal">Cancel</button><button type="submit" class="px-5 py-2.5 bg-accent-500 text-white rounded-xl font-bold">Update</button></div></form></div></div></div>

    <?php include __DIR__.'/partials/footer.php'; ?>
    
    <script> const MUTCU = { user: <?=json_encode($currentUser)?>, books: <?=json_encode($books)?>, articles: <?=json_encode($articles)?>, stats: <?=json_encode($stats)?> };</script>
    <script src="assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- FIX: Added missing Chart.js script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx1 = document.getElementById('interactionsChart');
            const ctx2 = document.getElementById('categoryChart');
            if(ctx1 && ctx2 && typeof Chart !== 'undefined') {
                const days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                const weeklyData = <?=json_encode($weeklyData)?>;
                const categoryData = <?=json_encode($categoryData)?>;
                const categoryLabels = categoryData.map(item => item.category);
                const categoryCounts = categoryData.map(item => parseInt(item.count));

                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: days,
                        datasets: [{
                            label: 'Interactions',
                            data: weeklyData,
                            borderColor: '#f97316',
                            backgroundColor: 'rgba(249, 115, 22, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointBackgroundColor: '#0f172a'
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });

                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: categoryLabels,
                        datasets: [{
                            data: categoryCounts,
                            backgroundColor: ['#f43f5e','#f97316','#3b82f6','#10b981','#8b5cf6'],
                            borderWidth: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } }, cutout: '70%' }
                });
            }
        });

        // Search Handlers
        document.getElementById('searchBooksAdmin')?.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('#booksTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        });

        document.getElementById('searchArticlesAdmin')?.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('#articlesTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        });

        // FIX: Removed duplicated function declaration
        function populateEditBookModal(id, title, author, category, driveLink, description, cover) {
            document.getElementById('editBookId').value = id;
            document.getElementById('editBookTitle').value = title;
            document.getElementById('editBookAuthor').value = author;
            document.getElementById('editBookCategory').value = category;
            document.getElementById('editBookDriveLink').value = driveLink;
            document.getElementById('editBookDescription').value = description;
            document.getElementById('editBookCover').value = cover;
        }

        function populateEditArticleModal(id, title, author, abstract, link, date, readTime) {
            document.getElementById('editArticleId').value = id;
            document.getElementById('editArticleTitle').value = title;
            document.getElementById('editArticleAuthor').value = author;
            document.getElementById('editArticleAbstract').value = abstract;
            document.getElementById('editArticleLink').value = link;
            document.getElementById('editArticleDate').value = date;
            document.getElementById('editArticleReadTime').value = readTime;
        }
    </script>
</body>
</html>