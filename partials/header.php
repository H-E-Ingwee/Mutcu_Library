<?php
if (!isset($currentUser)) {
    $currentUser = currentUser();
}
?>
<nav class="navbar navbar-expand-lg bg-primary-brand sticky-top py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center fs-4" href="home.php">
            <i class="bi bi-book-half me-2"></i> MUTCU Library
        </a>
        <button class="navbar-toggler border-0 text-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <i class="bi bi-list fs-1"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="library.php">Books</a></li>
                <li class="nav-item"><a class="nav-link" href="articles.php">Articles</a></li>
                <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
                <?php if ($currentUser): ?>
                    <li class="nav-item"><a class="nav-link text-white" href="profile.php"><i class="bi bi-person-circle me-1"></i> My Library</a></li>
                    <li class="nav-item">
                        <form method="post" action="actions.php" class="m-0">
                            <input type="hidden" name="action" value="logout">
                            <input type="hidden" name="return_url" value="<?=basename($_SERVER['PHP_SELF'])?>">
                            <button type="submit" class="btn btn-accent px-4 py-2 rounded-pill">Logout</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-4 mt-3 mt-lg-0"><a class="btn btn-accent px-4 py-2 rounded-pill" href="login.php">Member Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
