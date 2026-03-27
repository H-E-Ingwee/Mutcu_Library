<footer class="bg-brand-900 text-slate-400 py-16 border-t border-brand-800 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-12">
            <div class="md:col-span-5">
                <h4 class="font-heading text-2xl font-extrabold text-white flex items-center mb-6">
                    <!-- Updated with the brand's teal highlight as a subtle detail -->
                    <i class="bi bi-book-half text-accent-500 mr-3"></i> MUTCU
                </h4>
                <p class="mb-6 leading-relaxed text-sm max-w-md">
                    A digital platform providing Murang'a University of Technology Christian Union members structured, easy, and copyright-compliant access to Christ-centered educational resources.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="https://www.facebook.com/people/Muranga-University-of-Technology-Christian-Union-1/100068859581695/" target="_blank" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-slate-300 hover:bg-accent-500 hover:text-white transition-all duration-300 text-decoration-none" title="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com/muranga_university_cu/" target="_blank" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-slate-300 hover:bg-accent-500 hover:text-white transition-all duration-300 text-decoration-none" title="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.tiktok.com/@mutcu001" target="_blank" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-slate-300 hover:bg-accent-500 hover:text-white transition-all duration-300 text-decoration-none" title="TikTok"><i class="bi bi-tiktok"></i></a>
                    <a href="https://www.youtube.com/@murangauniversityCU" target="_blank" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-slate-300 hover:bg-accent-500 hover:text-white transition-all duration-300 text-decoration-none" title="YouTube"><i class="bi bi-youtube"></i></a>
                    <a href="https://mutcu.org/" target="_blank" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-slate-300 hover:bg-accent-500 hover:text-white transition-all duration-300 text-decoration-none" title="Official Website"><i class="bi bi-globe"></i></a>
                </div>
            </div>
            
            <div class="md:col-span-3 md:col-start-7">
                <h5 class="text-white font-bold font-heading mb-6 tracking-wide">Quick Links</h5>
                <ul class="space-y-3 pl-0 list-none">
                    <li><a href="home.php" class="text-slate-400 hover:text-accent-500 transition-colors text-sm font-semibold text-decoration-none">Home</a></li>
                    <li><a href="library.php" class="text-slate-400 hover:text-accent-500 transition-colors text-sm font-semibold text-decoration-none">Browse Books</a></li>
                    <li><a href="articles.php" class="text-slate-400 hover:text-accent-500 transition-colors text-sm font-semibold text-decoration-none">Articles</a></li>
                    <?php if (isset($currentUser) && isAdmin()): ?>
                    <li><a href="admin.php" class="text-slate-400 hover:text-accent-500 transition-colors text-sm font-semibold text-decoration-none">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="md:col-span-3">
                <h5 class="text-white font-bold font-heading mb-6 tracking-wide">Legal & System</h5>
                <ul class="space-y-3 text-sm font-medium pl-0 list-none">
                    <!-- NEW POLICY LINK -->
                    <li>
                        <a href="policy.php" class="flex items-center text-slate-400 hover:text-accent-500 transition-colors text-decoration-none">
                            <i class="bi bi-shield-check mr-3 text-lg"></i> Policy & Copyright
                        </a>
                    </li>
                    <li class="flex items-start text-slate-400"><i class="bi bi-hdd-network mr-3 text-lg"></i> Hosted via G-Drive</li>
                    <li class="flex items-start text-slate-400"><i class="bi bi-code-square mr-3 text-lg"></i> v3.0 Tailwind Release</li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-slate-800 mt-16 pt-8 text-center text-sm font-medium flex flex-col md:flex-row justify-between items-center">
            <span>&copy; <?= date('Y') ?> MUTCU E-Library System. Proposed & Built by Brian Ingwee.</span>
            <span class="mt-2 md:mt-0 text-slate-500">For Educational & Internal Use Only.</span>
        </div>
    </div>
</footer>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-4 z-[9999]">
    <div id="flashToast" class="toast align-items-center text-white border-0 shadow-2xl rounded-xl" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body font-semibold font-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-white rounded-2xl border-0 shadow-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-100 bg-brand-50 p-4">
                <h5 class="modal-title font-heading font-bold text-brand-900" id="quickViewModalLabel">Book Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="w-full md:w-1/3">
                        <div class="rounded-xl overflow-hidden shadow-md bg-slate-100 relative pt-[140%]">
                            <img id="quickViewCover" src="" class="absolute inset-0 w-full h-full object-cover" alt="Book Cover">
                        </div>
                    </div>
                    <div class="w-full md:w-2/3 flex flex-col">
                        <span id="quickViewCategory" class="inline-block bg-brand-900 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider w-max mb-3"></span>
                        <h3 id="quickViewTitle" class="font-heading font-extrabold text-2xl text-brand-900 mb-1"></h3>
                        <p class="text-slate-500 font-medium mb-4 pb-4 border-b border-slate-100">By <span id="quickViewAuthor" class="text-slate-700"></span></p>
                        <p id="quickViewDescription" class="text-slate-600 leading-relaxed mb-6 flex-grow"></p>
                        
                        <div class="flex gap-3 mt-auto">
                            <input type="hidden" id="quickViewBookId">
                            <button onclick="toggleBookmark(document.getElementById('quickViewBookId').value, this)" id="quickViewBookmarkBtn" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-colors flex items-center bg-white">
                                <i class="bi bi-bookmark mr-2"></i> Save
                            </button>
                            <a id="quickViewDownloadBtn" href="" target="_blank" class="flex-grow px-5 py-2.5 rounded-xl bg-brand-900 hover:bg-brand-800 text-white font-bold transition-colors flex items-center justify-center text-decoration-none">
                                <i class="bi bi-cloud-arrow-down mr-2"></i> Access Resource
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const flashSuccess = '<?php echo addslashes($_SESSION['flash_success'] ?? ''); ?>';
        const flashError = '<?php echo addslashes($_SESSION['flash_error'] ?? ''); ?>';
        
        function showCustomToast(message, type) {
            const toastEl = document.getElementById('flashToast');
            const toastBody = document.getElementById('toastMessage');
            toastBody.textContent = message;
            
            toastEl.classList.remove('bg-emerald-600', 'bg-rose-600', 'bg-brand-800');
            
            if(type === 'success') {
                toastEl.classList.add('bg-emerald-600');
            } else if (type === 'danger') {
                toastEl.classList.add('bg-rose-600');
            } else {
                toastEl.classList.add('bg-brand-800');
            }
            
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        if (flashSuccess) {
            showCustomToast(flashSuccess, 'success');
            <?php if (isset($_SESSION['flash_success'])) unset($_SESSION['flash_success']); ?>
        }
        if (flashError) {
            showCustomToast(flashError, 'danger');
            <?php if (isset($_SESSION['flash_error'])) unset($_SESSION['flash_error']); ?>
        }
    });
</script>