<?php
require_once __DIR__ . '/functions.php';
$currentUser = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MUTCU E-Library | Policies & Copyright</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Official MUTCU Branding
        tailwind.config = { theme: { extend: { fontFamily: { heading: ['Montserrat', 'sans-serif'], body: ['Lato', 'sans-serif'], }, colors: { brand: { 900: '#060B26', 800: '#0B133A', 50: '#F4F6FB', }, accent: { 500: '#FF9800', 600: '#E68A00', }, mutcu: { teal: '#2DD4BF', red: '#FF1A35' } } } } }
    </script>
    <style> h1, h2, h3, h4, h5, h6 { font-family: 'Montserrat', sans-serif; } </style>
</head>
<body class="font-body bg-brand-50 flex flex-col min-h-screen text-slate-800">
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <main class="flex-grow py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center mb-12">
                <span class="inline-block p-3 rounded-full bg-brand-900 text-accent-500 mb-4">
                    <i class="bi bi-shield-check text-4xl"></i>
                </span>
                <h1 class="text-4xl font-extrabold font-heading text-brand-900 mb-4">Legal & Copyright Policies</h1>
                <p class="text-slate-500 text-lg">Terms of Service, Privacy Policy, and Copyright Protections for the Murang'a University of Technology Christian Union E-Library.</p>
                <p class="text-sm text-slate-400 mt-2">Last Updated: <?= date('F j, Y') ?></p>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                
                <!-- Section 1: Copyright -->
                <div class="p-8 sm:p-10 border-b border-slate-100">
                    <h2 class="text-2xl font-bold font-heading text-brand-900 mb-4 flex items-center">
                        <i class="bi bi-c-circle text-accent-500 mr-3"></i> 1. Copyright Protection & Fair Use
                    </h2>
                    <div class="prose prose-slate max-w-none text-slate-600 space-y-4">
                        <p>
                            The MUTCU E-Library serves as an internal, closed-network educational platform designed exclusively for the spiritual and academic edification of registered members of the Murang'a University of Technology Christian Union.
                        </p>
                        <p>
                            <strong>Educational Fair Use:</strong> Links provided within this system (including Google Drive URLs) route to resources hosted externally. These resources are compiled and indexed strictly for non-commercial, educational, and religious study purposes under the provisions of <em>Fair Use</em>.
                        </p>
                        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 mt-4 rounded-r-lg">
                            <h4 class="font-bold text-rose-800 mb-1">Strictly Prohibited Uses:</h4>
                            <ul class="list-disc pl-5 text-rose-700 text-sm space-y-1">
                                <li>Commercial redistribution, selling, or printing of digital materials accessed via this platform.</li>
                                <li>Sharing direct resource links to non-registered individuals or public internet forums.</li>
                                <li>Automated scraping, downloading, or archiving of the library catalog.</li>
                            </ul>
                        </div>
                        <p class="text-sm mt-4 italic">
                            Disclaimer: MUTCU does not claim ownership over the intellectual property of the authors featured in this library. If you are a copyright holder and believe your work has been inappropriately indexed, please contact the MUTCU Executive Committee for immediate review and removal.
                        </p>
                    </div>
                </div>

                <!-- Section 2: Terms of Service -->
                <div class="p-8 sm:p-10 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-2xl font-bold font-heading text-brand-900 mb-4 flex items-center">
                        <i class="bi bi-file-text text-accent-500 mr-3"></i> 2. Terms of Service
                    </h2>
                    <div class="prose prose-slate max-w-none text-slate-600 space-y-4">
                        <p>
                            By creating an account and accessing the MUTCU E-Library, you agree to abide by the following terms:
                        </p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li><strong>Account Security:</strong> You are responsible for maintaining the confidentiality of your login credentials. Do not share your account.</li>
                            <li><strong>Appropriate Conduct:</strong> Members must act in accordance with Christian values. Any attempt to hack, disrupt, or manipulate the platform's code or database will result in immediate account termination.</li>
                            <li><strong>Resource Availability:</strong> MUTCU reserves the right to add, modify, or remove access to any book or article at any time without prior notice.</li>
                        </ul>
                    </div>
                </div>

                <!-- Section 3: Privacy Policy -->
                <div class="p-8 sm:p-10">
                    <h2 class="text-2xl font-bold font-heading text-brand-900 mb-4 flex items-center">
                        <i class="bi bi-shield-lock text-accent-500 mr-3"></i> 3. Privacy Policy
                    </h2>
                    <div class="prose prose-slate max-w-none text-slate-600 space-y-4">
                        <p>
                            Your privacy is important to us. The MUTCU E-Library collects minimal personal data required for the operation of the system.
                        </p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li><strong>Data Collected:</strong> We securely store your Name, Email Address, securely hashed passwords, bookmarks, and reading history.</li>
                            <li><strong>Data Usage:</strong> Your data is used exclusively to provide a personalized experience (e.g., Reading Goals, "Recently Accessed" lists) and generate anonymized administrative analytics to understand library usage trends.</li>
                            <li><strong>No Third-Party Sharing:</strong> We will never sell, rent, or distribute your personal information to third parties.</li>
                        </ul>
                        <p class="mt-4">
                            You may request to have your account and associated data permanently deleted by contacting the system administrator.
                        </p>
                    </div>
                </div>

                <!-- Section 4: Official Channels -->
                <div class="p-8 sm:p-10 border-t border-slate-100 bg-slate-50">
                    <h2 class="text-2xl font-bold font-heading text-brand-900 mb-4 flex items-center">
                        <i class="bi bi-link-45deg text-accent-500 mr-3"></i> 4. Official Communication Channels
                    </h2>
                    <div class="prose prose-slate max-w-none text-slate-600 space-y-4">
                        <p>
                            Stay connected with the Murang'a University of Technology Christian Union through our official digital platforms and social media channels:
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <a href="https://mutcu.org/" target="_blank" class="flex items-center p-3 rounded-xl border border-slate-200 hover:border-accent-500 hover:shadow-md transition-all text-decoration-none text-slate-700 group bg-white">
                                <div class="w-10 h-10 rounded-full bg-brand-50 flex items-center justify-center text-brand-900 group-hover:bg-accent-500 group-hover:text-white transition-colors mr-3"><i class="bi bi-globe"></i></div>
                                <span class="font-bold">Official Website</span>
                            </a>
                            <a href="https://www.youtube.com/@murangauniversityCU" target="_blank" class="flex items-center p-3 rounded-xl border border-slate-200 hover:border-rose-500 hover:shadow-md transition-all text-decoration-none text-slate-700 group bg-white">
                                <div class="w-10 h-10 rounded-full bg-rose-50 flex items-center justify-center text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition-colors mr-3"><i class="bi bi-youtube"></i></div>
                                <span class="font-bold">YouTube Channel</span>
                            </a>
                            <a href="https://www.instagram.com/muranga_university_cu/" target="_blank" class="flex items-center p-3 rounded-xl border border-slate-200 hover:border-pink-500 hover:shadow-md transition-all text-decoration-none text-slate-700 group bg-white">
                                <div class="w-10 h-10 rounded-full bg-pink-50 flex items-center justify-center text-pink-600 group-hover:bg-pink-600 group-hover:text-white transition-colors mr-3"><i class="bi bi-instagram"></i></div>
                                <span class="font-bold">Instagram</span>
                            </a>
                            <a href="https://www.tiktok.com/@mutcu001" target="_blank" class="flex items-center p-3 rounded-xl border border-slate-200 hover:border-slate-900 hover:shadow-md transition-all text-decoration-none text-slate-700 group bg-white">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-900 group-hover:bg-slate-900 group-hover:text-white transition-colors mr-3"><i class="bi bi-tiktok"></i></div>
                                <span class="font-bold">TikTok</span>
                            </a>
                            <a href="https://www.facebook.com/people/Muranga-University-of-Technology-Christian-Union-1/100068859581695/" target="_blank" class="flex items-center p-3 rounded-xl border border-slate-200 hover:border-blue-500 hover:shadow-md transition-all text-decoration-none text-slate-700 group bg-white">
                                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors mr-3"><i class="bi bi-facebook"></i></div>
                                <span class="font-bold">Facebook Page</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
            
            <div class="text-center mt-12">
                <a href="home.php" class="inline-flex items-center px-6 py-3 bg-brand-900 hover:bg-brand-800 text-white rounded-full font-bold transition-colors shadow-lg shadow-brand-900/30 text-decoration-none">
                    <i class="bi bi-arrow-left mr-2"></i> Return to Homepage
                </a>
            </div>

        </div>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>
    <script src="assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>