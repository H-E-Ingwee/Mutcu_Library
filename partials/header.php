<?php
if (!isset($currentUser)) {
    $currentUser = currentUser();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- GLOBAL CSRF TOKEN FOR SECURE FORMS & AJAX -->
<input type="hidden" id="csrf_token_global" value="<?= csrf_token() ?>">

<nav class="sticky top-0 z-50 bg-brand-900/95 backdrop-blur-md border-b border-white/10 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo -->
            <a class="flex items-center text-white hover:text-white text-xl font-extrabold font-heading tracking-tight text-decoration-none" href="home.php">
                <i class="bi bi-book-half text-accent-500 mr-2 text-2xl"></i> MUTCU Library
            </a>
            
            <!-- Mobile menu button -->
            <button class="md:hidden text-slate-300 hover:text-white focus:outline-none bg-transparent border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu">
                <i class="bi bi-list text-3xl"></i>
            </button>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-1">
                <a class="px-4 py-2 rounded-lg font-bold text-sm transition-colors <?= $current_page == 'home.php' ? 'text-accent-500 bg-white/5' : 'text-slate-300 hover:text-accent-500 hover:bg-white/5' ?> text-decoration-none" href="home.php">Home</a>
                <a class="px-4 py-2 rounded-lg font-bold text-sm transition-colors <?= $current_page == 'library.php' ? 'text-accent-500 bg-white/5' : 'text-slate-300 hover:text-accent-500 hover:bg-white/5' ?> text-decoration-none" href="library.php">Books</a>
                <a class="px-4 py-2 rounded-lg font-bold text-sm transition-colors <?= $current_page == 'articles.php' ? 'text-accent-500 bg-white/5' : 'text-slate-300 hover:text-accent-500 hover:bg-white/5' ?> text-decoration-none" href="articles.php">Articles</a>
                <?php if ($currentUser && isAdmin()): ?>
                <a class="px-4 py-2 rounded-lg font-bold text-sm transition-colors <?= $current_page == 'admin.php' ? 'text-accent-500 bg-white/5' : 'text-slate-300 hover:text-accent-500 hover:bg-white/5' ?> text-decoration-none" href="admin.php">Admin</a>
                <?php endif; ?>
                
                <div class="w-px h-6 bg-slate-700 mx-2"></div>
                
                <?php if ($currentUser): ?>
                    <div class="dropdown">
                        <button class="flex items-center space-x-2 px-4 py-2 rounded-lg font-bold text-sm text-white hover:bg-white/5 transition-colors border-0 bg-transparent dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle text-lg text-accent-500"></i>
                            <span><?=htmlspecialchars($currentUser['name'])?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end bg-brand-800 border border-slate-700 shadow-xl rounded-xl" aria-labelledby="userMenu">
                            <li><a class="dropdown-item hover:bg-accent-500 hover:text-white font-semibold transition-colors py-2 flex items-center" href="profile.php"><i class="bi bi-book mr-2"></i> My Library</a></li>
                            <li><hr class="dropdown-divider border-slate-700"></li>
                            <li>
                                <form method="post" action="actions.php" class="m-0">
                                    <input type="hidden" name="action" value="logout">
                                    <input type="hidden" name="return_url" value="<?=basename($_SERVER['PHP_SELF'])?>">
                                    <button type="submit" class="dropdown-item text-rose-400 hover:bg-rose-500 hover:text-white font-semibold transition-colors py-2 flex items-center border-0 bg-transparent w-full text-left"><i class="bi bi-box-arrow-right mr-2"></i> Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="ml-4 px-6 py-2 bg-accent-500 hover:bg-accent-600 text-white rounded-full font-bold text-sm transition-colors shadow-lg shadow-accent-500/30 text-decoration-none" href="login.php">Member Login</a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Mobile Menu (Bootstrap Collapse mapped with Tailwind) -->
        <div class="collapse md:hidden" id="mobileMenu">
            <div class="flex flex-col space-y-2 pb-4 pt-2 border-t border-slate-800">
                <a class="px-4 py-2 rounded-lg font-bold text-sm <?= $current_page == 'home.php' ? 'text-accent-500 bg-white/5' : 'text-slate-300' ?> text-decoration-none" href="home.php">Home</a>
                <a class="px-4 py-2 rounded-lg font-bold text-sm <?= $current_page == 'library.php' ? 'text-accent-500 bg-white/5' : 'text-slate-300' ?> text-decoration-none" href="library.php">Books</a>
                <a class="px-4 py-2 rounded-lg font-bold text-sm <?= $current_page == 'articles.php' ? 'text-accent-500 bg-white/5' : 'text-slate-300' ?> text-decoration-none" href="articles.php">Articles</a>
                <?php if ($currentUser && isAdmin()): ?>
                <a class="px-4 py-2 rounded-lg font-bold text-sm <?= $current_page == 'admin.php' ? 'text-accent-500 bg-white/5' : 'text-slate-300' ?> text-decoration-none" href="admin.php">Admin</a>
                <?php endif; ?>
                
                <div class="border-t border-slate-800 pt-4 mt-2">
                    <?php if ($currentUser): ?>
                        <a class="px-4 py-2 block rounded-lg font-bold text-sm text-slate-300 text-decoration-none" href="profile.php"><i class="bi bi-book mr-2"></i> My Library</a>
                        <form method="post" action="actions.php" class="m-0 px-4 py-2">
                            <input type="hidden" name="action" value="logout">
                            <input type="hidden" name="return_url" value="<?=basename($_SERVER['PHP_SELF'])?>">
                            <button type="submit" class="w-full text-left font-bold text-sm text-rose-400 bg-transparent border-0 p-0"><i class="bi bi-box-arrow-right mr-2"></i> Logout</button>
                        </form>
                    <?php else: ?>
                        <a class="mx-4 px-6 py-2 block text-center bg-accent-500 text-white rounded-full font-bold text-sm text-decoration-none mt-2" href="login.php">Member Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</nav>