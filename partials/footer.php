<footer class="mt-auto">
    <div class="container">
        <div class="row gy-5">
            <div class="col-lg-5 pe-lg-5">
                <h4 class="heading-font d-flex align-items-center mb-4">
                    <i class="bi bi-book-half me-2" style="color: var(--accent-color);"></i> MUTCU
                </h4>
                <p class="mb-4 text-white-50" style="line-height: 1.8;">
                    A digital platform providing Muranga University of Technology Christian Union members structured, easy, and copyright-compliant access to Christ-centered educational resources.
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-white-50 fs-4 hover-accent"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white-50 fs-4 hover-accent"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-white-50 fs-4 hover-accent"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 offset-lg-1">
                <h5 class="mb-4 position-relative inline-block">Quick Links</h5>
                <ul class="list-unstyled mt-4">
                    <li class="mb-3"><a href="home.php" class="footer-link">Home</a></li>
                    <li class="mb-3"><a href="library.php" class="footer-link">Browse Books</a></li>
                    <li class="mb-3"><a href="articles.php" class="footer-link">Articles</a></li>
                    <li class="mb-3"><a href="admin.php" class="footer-link">Admin Panel</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="mb-4 position-relative inline-block">System Details</h5>
                <ul class="list-unstyled mt-4 text-white-50">
                    <li class="mb-3"><i class="bi bi-hdd-network me-2 text-warning"></i> Hosted via Google Drive</li>
                    <li class="mb-3"><i class="bi bi-shield-check me-2 text-success"></i> Copyright Compliant</li>
                    <li class="mb-3"><i class="bi bi-code-square me-2 text-info"></i> v2.0 Prototype</li>
                </ul>
            </div>
        </div>
        <div class="text-center border-top border-secondary pt-4 mt-5 text-white-50 small">
            &copy; 2026 MUTCU E-Library System. Proposed & Built by Brian Ingwee. All Rights Reserved.
        </div>
    </div>
</footer>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="flashToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickViewModalLabel">Book Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <img id="quickViewCover" src="" class="img-fluid rounded" alt="Book Cover">
                    </div>
                    <div class="col-md-8">
                        <span id="quickViewCategory" class="badge bg-primary mb-2"></span>
                        <h4 id="quickViewTitle" class="fw-bold"></h4>
                        <p class="text-muted mb-3">By <span id="quickViewAuthor"></span></p>
                        <p id="quickViewDescription" class="mb-4"></p>
                        <div class="d-flex gap-2">
                            <button onclick="toggleBookmark(document.getElementById('quickViewBookId').value, this)" id="quickViewBookmarkBtn" class="btn btn-outline-secondary rounded-pill fw-bold">
                                <i class="bi bi-bookmark me-1"></i> Bookmark
                            </button>
                            <a id="quickViewDownloadBtn" href="" target="_blank" class="btn btn-primary rounded-pill fw-bold">
                                <i class="bi bi-cloud-arrow-down me-1"></i> Access Book
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
<script>
    // Show flash messages as toasts
    document.addEventListener('DOMContentLoaded', function() {
        const flashSuccess = '<?php echo addslashes($_SESSION['flash_success'] ?? ''); ?>';
        const flashError = '<?php echo addslashes($_SESSION['flash_error'] ?? ''); ?>';
        if (flashSuccess) {
            showToast(flashSuccess, 'success');
            <?php if (isset($_SESSION['flash_success'])) unset($_SESSION['flash_success']); ?>
        }
        if (flashError) {
            showToast(flashError, 'danger');
            <?php if (isset($_SESSION['flash_error'])) unset($_SESSION['flash_error']); ?>
        }
    });
</script>
